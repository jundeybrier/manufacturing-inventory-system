<div class="text-center mt-6">
    <button
        wire:click="increase"
        wire:keydown.prevent
        class="cursor-pointer select-none focus:outline-none px-6 py-3 bg-pink-600 hover:bg-pink-700 text-white rounded-full shadow-lg transform hover:scale-105 active:scale-95 transition-all duration-200 cursor-pointer">
        😤 Click Me For Stress Relief!
    </button>

    <div class="mt-3 text-lg font-bold text-zinc-700 dark:text-zinc-300">
        Total Clicks: <span class="text-pink-600">{{ $clickCount }}</span>
    </div>

    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 italic">
        Stress level measured in clicks 😂
    </p>
</div>
