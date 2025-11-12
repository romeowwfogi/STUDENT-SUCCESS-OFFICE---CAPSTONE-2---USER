// Announcements carousel: minimal, accessible controls
(function() {
  const carousel = document.getElementById('announcements-carousel');
  if (!carousel) return;

  const prevBtn = document.querySelector('.carousel_btn.prev');
  const nextBtn = document.querySelector('.carousel_btn.next');

  function scrollByCards(direction) {
    const card = carousel.querySelector('.announcement_card');
    const scrollAmount = card ? (card.offsetWidth + 16) * 2 : Math.floor(carousel.clientWidth * 0.8);
    carousel.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
  }

  prevBtn && prevBtn.addEventListener('click', () => scrollByCards(-1));
  nextBtn && nextBtn.addEventListener('click', () => scrollByCards(1));

  // Keyboard support when carousel is focused
  carousel.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') {
      e.preventDefault();
      scrollByCards(-1);
    } else if (e.key === 'ArrowRight') {
      e.preventDefault();
      scrollByCards(1);
    }
  });

  // Duplicate cards to create a seamless loop
  if (!carousel.dataset.loopReady) {
    const originals = Array.from(carousel.children);
    originals.forEach((node) => {
      carousel.appendChild(node.cloneNode(true));
    });
    carousel.dataset.loopReady = 'true';
  }

  // Base width equals original content width (half of the duplicated scrollWidth)
  let baseWidth = Math.floor(carousel.scrollWidth / 2);

  // Autoplay using rAF for smooth continuous scrolling
  let running = true;
  let lastTs;
  const speedPxPerFrame = 1; // faster movement for clearer horizontal animation

  function step(ts) {
    if (!lastTs) lastTs = ts;
    const delta = ts - lastTs;
    lastTs = ts;
    if (running) {
      const increment = speedPxPerFrame * (delta / 16.67); // normalize to ~60fps
      carousel.scrollLeft += increment;
      if (carousel.scrollLeft >= baseWidth) {
        // wrap seamlessly
        carousel.scrollLeft -= baseWidth;
      }
    }
    requestAnimationFrame(step);
  }

  requestAnimationFrame(step);

  // Pause/resume autoplay when hovering
carousel.addEventListener('mouseenter', () => {
  running = false;
});

carousel.addEventListener('mouseleave', () => {
  running = true;
});


  // Recompute baseWidth on resize (in case of responsive changes)
  window.addEventListener('resize', () => {
    baseWidth = Math.floor(carousel.scrollWidth / 2);
  });

  // Also observe carousel size/content changes to keep baseWidth accurate
  if (window.ResizeObserver) {
    const ro = new ResizeObserver(() => {
      baseWidth = Math.floor(carousel.scrollWidth / 2);
    });
    ro.observe(carousel);
  }
})();