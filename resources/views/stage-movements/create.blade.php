<x-layouts.app :title="__('Dashboard')">
    <div class="max-w-4xl mx-auto p-6"
         x-data="stageMovement(
        {{ $items->toJson() }},
        {{ $stages->toJson() }}
     )">

        <h1 class="text-xl font-bold mb-6">Record Stage Movement</h1>

        <form action="{{ route('stage-movements.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- ITEM --}}
            <div>
                <label class="block font-semibold mb-1">Item</label>
                <select name="inventory_item_id"
                        x-model="itemId"
                        @change="loadStages"
                        class="w-full px-3 py-2 rounded border border-gray-300
       dark:border-gray-700 bg-white dark:bg-gray-800
       text-gray-800 dark:text-gray-200
       focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Select Item --</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- FROM STAGE --}}
            <div>
                <label class="block font-semibold mb-1">From Stage</label>
                <select name="from_stage_id"
                        x-model="fromStageId"
                        @change="fetchQuantity('from')"
                        class="w-full px-3 py-2 rounded border border-gray-300
       dark:border-gray-700 bg-white dark:bg-gray-800
       text-gray-800 dark:text-gray-200
       focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- (optional) Incoming stock --</option>
                    <template x-for="s in filteredStages">
                        <option :value="s.id" x-text="s.name"></option>
                    </template>
                </select>

                <p class="text-sm text-gray-600" x-show="fromQty !== null">
                    Current Qty: <span class="font-bold" x-text="fromQty"></span><br>
                    After Move: <span class="font-bold text-red-600" x-text="fromAfter"></span>
                </p>
            </div>

            {{-- TO STAGE --}}
            <div>
                <label class="block font-semibold mb-1">To Stage</label>
                <select name="to_stage_id"
                        x-model="toStageId"
                        @change="fetchQuantity('to')"
                        class="w-full px-3 py-2 rounded border border-gray-300
       dark:border-gray-700 bg-white dark:bg-gray-800
       text-gray-800 dark:text-gray-200
       focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- (optional) Outgoing to scrap --</option>

                    <template x-for="s in filteredStages">
                        <option :value="s.id" x-text="s.name"></option>
                    </template>
                </select>

                <p class="text-sm text-gray-600" x-show="toQty !== null">
                    Current Qty: <span class="font-bold" x-text="toQty"></span><br>
                    After Move: <span class="font-bold text-green-600" x-text="toAfter"></span>
                </p>
            </div>

            {{-- QUANTITY --}}
            <div>
                <label class="block font-semibold mb-1">Quantity</label>
                <input type="number"
                       step="0.001"
                       min="0.001"
                       name="quantity"
                       x-model="qty"
                       @input="updateRealtime"
                       class="w-full border rounded px-3 py-2" />
            </div>

            {{-- DATE --}}
            <div>
                <label class="block font-semibold mb-1">Movement Date</label>
                <input type="date" name="movement_date"
                       value="{{ now()->toDateString() }}"
                       class="w-full border rounded px-3 py-2">
            </div>

            {{-- REMARKS --}}
            <div>
                <label class="block font-semibold mb-1">Remarks</label>
                <input type="text" name="remarks" class="w-full border rounded px-3 py-2">
            </div>

            <button class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Save Movement
            </button>
        </form>
    </div>

    <script>
        function stageMovement(items, stages) {
            return {
                items,
                stages,

                itemId: '',
                filteredStages: [],
                fromStageId: '',
                toStageId: '',

                qty: 0,

                fromQty: null,
                fromAfter: null,

                toQty: null,
                toAfter: null,

                // Filter stages based on item category
                loadStages() {
                    const item = this.items.find(i => i.id == this.itemId);
                    if (!item) return;

                    this.filteredStages = this.stages.filter(s => s.category === item.category);
                },

                // Fetch current qty of FROM/TO stage in real-time
                async fetchQuantity(type) {
                    let stageId = type === 'from' ? this.fromStageId : this.toStageId;

                    if (!stageId || !this.itemId) return;

                    const resp = await fetch(`/api/stage-inventory/${this.itemId}/${stageId}`);
                    const data = await resp.json();

                    if (type === 'from') {
                        this.fromQty = parseFloat(data.current_qty);
                        this.updateRealtime();
                    } else {
                        this.toQty = parseFloat(data.current_qty);
                        this.updateRealtime();
                    }
                },

                updateRealtime() {
                    this.fromAfter = (this.fromQty !== null)
                        ? (this.fromQty - parseFloat(this.qty || 0))
                        : null;

                    this.toAfter = (this.toQty !== null)
                        ? (this.toQty + parseFloat(this.qty || 0))
                        : null;
                }
            }
        }
    </script>
</x-layouts.app>>
