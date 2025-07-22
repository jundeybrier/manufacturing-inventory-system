<div
    x-data="{ show: false, message: '', type: 'info' }"
    x-init="
        window.addEventListener('toast', event => {
            message = event.detail.message || 'No message';
            type = event.detail.type || 'info';
            show = true;
            setTimeout(() => show = false, 4000);
        });
    "
    x-show="show"
    x-transition.duration.300ms
    class="fixed bottom-6 right-6 z-50 w-[22rem] flex items-start gap-3 rounded-xl px-4 py-3 shadow-lg text-white"
    :class="{
        'bg-blue-600': type === 'info',
        'bg-green-600': type === 'success',
        'bg-red-600': type === 'error',
        'bg-yellow-500': type === 'warning',
    }"
    style="display: none;"
>
    <!-- Icon -->
    <div class="shrink-0 pt-0.5">
        <svg x-show="type === 'info'" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2">
            <use href="#icon-info" />
        </svg>
        <svg x-show="type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2">
            <use href="#icon-check" />
        </svg>
        <svg x-show="type === 'error'" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2">
            <use href="#icon-x" />
        </svg>
        <svg x-show="type === 'warning'" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2">
            <use href="#icon-alert" />
        </svg>
    </div>

    <!-- Message -->
    <div class="flex-1 text-sm font-medium leading-snug" x-text="message"></div>

    <!-- Close button -->
    <button @click="show = false" class="ml-2 text-white hover:text-opacity-80 focus:outline-none">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>

    <!-- Icons (hidden in DOM for use with <use>) -->
    <svg style="display:none">
        <symbol id="icon-info" viewBox="0 0 24 24">
            <path d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z" stroke-linecap="round" stroke-linejoin="round"/>
        </symbol>
        <symbol id="icon-check" viewBox="0 0 24 24">
            <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/>
        </symbol>
        <symbol id="icon-x" viewBox="0 0 24 24">
            <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
        </symbol>
        <symbol id="icon-alert" viewBox="0 0 24 24">
            <path d="M12 9v2m0 4h.01m-6.938 4h13.856C18.07 19 19 18.07 19 16.938V7.062C19 5.93 18.07 5 16.938 5H7.062C5.93 5 5 5.93 5 7.062v9.876C5 18.07 5.93 19 7.062 19z" stroke-linecap="round" stroke-linejoin="round"/>
        </symbol>
    </svg>
</div>
