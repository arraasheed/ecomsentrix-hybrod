{{-- 
    Time Counter Component - Flash Sale Countdown
    CLAUDE-DEMO Phase 3: Time Counter UI
    
    Kill-switch: DEMO_THEME_ENABLED=false in .env
    
    Props:
      - deadline: DateTime|string (ISO 8601 or Carbon date)
      - label: string (default: 'Flash Sale Ends In')
      - showLabel: bool (default: true)
--}}
@props(['deadline', 'label' => 'Flash Sale Ends In', 'showLabel' => true])

@php
  $deadlineTime = is_string($deadline) ? strtotime($deadline) : $deadline->timestamp;
  $now = now()->timestamp;
  $secondsLeft = max(0, $deadlineTime - $now);
@endphp

<div class="time-counter-wrapper" data-deadline="{{ $deadline }}" data-turbo="true">
  @if ($showLabel)
    <p class="text-sm font-medium text-gray-700 mb-2 text-center">
      {{ $label }}
    </p>
  @endif

  <div class="time-container flex justify-center items-center gap-2 bg-white max-w-md mx-auto">
    {{-- Hours --}}
    <div class="time-counter-item flex flex-col justify-center items-center border-2 border-magenta-500 rounded px-3 py-2 min-w-16">
      <span class="time-counter-time text-magenta-500 font-bold text-lg" data-unit="hours">
        {{ str_pad(floor($secondsLeft / 3600), 2, '0', STR_PAD_LEFT) }}
      </span>
      <span class="time-counter-text text-magenta-500 text-xs font-medium">Hours</span>
    </div>

    {{-- Minutes --}}
    <div class="time-counter-item flex flex-col justify-center items-center border-2 border-magenta-500 rounded px-3 py-2 min-w-16">
      <span class="time-counter-time text-magenta-500 font-bold text-lg" data-unit="minutes">
        {{ str_pad(floor(($secondsLeft % 3600) / 60), 2, '0', STR_PAD_LEFT) }}
      </span>
      <span class="time-counter-text text-magenta-500 text-xs font-medium">Minutes</span>
    </div>

    {{-- Seconds --}}
    <div class="time-counter-item flex flex-col justify-center items-center border-2 border-magenta-500 rounded px-3 py-2 min-w-16">
      <span class="time-counter-time text-magenta-500 font-bold text-lg" data-unit="seconds">
        {{ str_pad($secondsLeft % 60, 2, '0', STR_PAD_LEFT) }}
      </span>
      <span class="time-counter-text text-magenta-500 text-xs font-medium">Seconds</span>
    </div>
  </div>
</div>

{{-- Inline JS for timer - wrapped in onTurboLoad for Turbo compatibility --}}
<script type="module">
  // Time counter update logic - Turbo-safe
  window.onTurboLoad((page) => {
    const timeCounters = document.querySelectorAll('.time-counter-wrapper');
    
    const updateCounters = () => {
      timeCounters.forEach(counter => {
        const deadline = new Date(counter.dataset.deadline).getTime();
        const now = new Date().getTime();
        const distance = Math.max(0, deadline - now);
        
        const hours = Math.floor((distance / (1000 * 60 * 60)) % 24);
        const minutes = Math.floor((distance / 1000 / 60) % 60);
        const seconds = Math.floor((distance / 1000) % 60);
        
        const hoursSpan = counter.querySelector('[data-unit="hours"]');
        const minutesSpan = counter.querySelector('[data-unit="minutes"]');
        const secondsSpan = counter.querySelector('[data-unit="seconds"]');
        
        if (hoursSpan) hoursSpan.textContent = String(hours).padStart(2, '0');
        if (minutesSpan) minutesSpan.textContent = String(minutes).padStart(2, '0');
        if (secondsSpan) secondsSpan.textContent = String(seconds).padStart(2, '0');
        
        // If countdown finished
        if (distance === 0) {
          counter.classList.add('opacity-50', 'pointer-events-none');
        }
      });
    };
    
    // Update immediately and then every second
    updateCounters();
    const interval = setInterval(updateCounters, 1000);
    
    // Cleanup on page cache (Turbo unload)
    window.onTurboUnload(() => {
      clearInterval(interval);
    });
  });
</script>
