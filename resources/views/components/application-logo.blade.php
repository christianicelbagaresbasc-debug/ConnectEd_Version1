<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2']) }}>
    <svg class="w-9 h-9 flex-shrink-0" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Rounded square container with teal gradient -->
        <rect width="40" height="40" rx="10" fill="url(#brandGradient)" />
        <!-- Node connections symbolizing skill-based placement -->
        <circle cx="14" cy="14" r="3.5" fill="#FFFFFF" />
        <circle cx="26" cy="14" r="3.5" fill="#FB923C" />
        <circle cx="20" cy="26" r="4.5" fill="#FFFFFF" />
        <path d="M14 14L20 26M26 14L20 26M14 14H26" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        <!-- Accent indicator -->
        <circle cx="20" cy="26" r="2" fill="#0F7C80" />
        <defs>
            <linearGradient id="brandGradient" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
                <stop stop-color="#0F7C80" />
                <stop offset="1" stop-color="#084E51" />
            </linearGradient>
        </defs>
    </svg>
    <div class="flex flex-col">
        <span class="font-heading font-bold text-lg leading-tight tracking-tight text-text">
            Connect<span class="text-primary">Ed</span>
        </span>
        <span class="text-[10px] uppercase tracking-wider font-semibold text-text-muted">
            BSAU ICS OJT
        </span>
    </div>
</div>
