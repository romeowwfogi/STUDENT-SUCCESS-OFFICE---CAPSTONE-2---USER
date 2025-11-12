
function togglePassword() {
  const passwordInput = document.getElementById('password');
  const eyeIcon = document.getElementById('eye-icon');
  const eyeOffIcon = document.getElementById('eye-off-icon');

  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    eyeIcon.style.display = 'none';
    eyeOffIcon.style.display = 'block';
  } else {
    passwordInput.type = 'password';
    eyeIcon.style.display = 'block';
    eyeOffIcon.style.display = 'none';
  }
}

// ================== PARTICLE EFFECT ==================
// Wrap in IIFE to avoid global collisions when included on multiple pages
(function() {
  // Target the landing page section if present; otherwise fallback to .bg (admission page)
  const containerEl = document.querySelector('.landingpage') || document.querySelector('.bg');
  const canvas = document.getElementById("canvas");
  if (!canvas) return; // safely exit if canvas not present on this page
  const ctx = canvas.getContext("2d");

const particles = [];
const fireworkParticles = [];
const dustParticles = [];
const ripples = [];
const techRipples = [];

const mouse = (() => {
  let state = { x: null, y: null };
  return {
    get x() { return state.x; },
    get y() { return state.y; },
    set({ x, y }) { state = { x, y }; },
    reset() { state = { x: null, y: null }; }
  };
})();

let frameCount = 0;
let autoDrift = true;

function adjustParticleCount() {
  const particleConfig = {
    heightConditions: [200, 300, 400, 500, 600],
    widthConditions: [450, 600, 900, 1200, 1600],
    particlesForHeight: [20, 30, 40, 50, 60],
    particlesForWidth: [20, 30, 40, 50, 60]
  };

  let numParticles = 60;
  for (let i = 0; i < particleConfig.heightConditions.length; i++) {
    if (canvas.height < particleConfig.heightConditions[i]) {
      numParticles = particleConfig.particlesForHeight[i];
      break;
    }
  }

  for (let i = 0; i < particleConfig.widthConditions.length; i++) {
    if (canvas.width < particleConfig.widthConditions[i]) {
      numParticles = Math.min(numParticles, particleConfig.particlesForWidth[i]);
      break;
    }
  }

  return numParticles;
}

class Particle {
  constructor(x, y, isFirework = false) {
    const baseSpeed = isFirework ? Math.random() * 2 + 1 : Math.random() * 0.5 + 0.3;

    Object.assign(this, {
      isFirework,
      x,
      y,
      vx: Math.cos(Math.random() * Math.PI * 2) * baseSpeed,
      vy: Math.sin(Math.random() * Math.PI * 2) * baseSpeed,
      size: isFirework ? Math.random() * 1 + 1 : Math.random() * 1.5 + 0.5,
      // Restrict particle hue to a yellow band (approx 50–65 deg)
      hue: 50 + Math.random() * 15,
      alpha: 1,
      sizeDirection: Math.random() < 0.5 ? -1 : 1,
      trail: []
    });
  }

  update(mouse) {
    const dist = mouse.x !== null ? (mouse.x - this.x) ** 2 + (mouse.y - this.y) ** 2 : 0;
    if (!this.isFirework) {
      const force = dist && dist < 22500 ? (22500 - dist) / 22500 : 0;

      if (mouse.x === null && autoDrift) {
        this.vx += (Math.random() - 0.5) * 0.03;
        this.vy += (Math.random() - 0.5) * 0.03;
      }

      if (dist) {
        const sqrtDist = Math.sqrt(dist);
        this.vx += ((mouse.x - this.x) / sqrtDist) * force * 0.1;
        this.vy += ((mouse.y - this.y) / sqrtDist) * force * 0.1;
      }

      this.vx *= mouse.x !== null ? 0.99 : 0.998;
      this.vy *= mouse.y !== null ? 0.99 : 0.998;
    } else {
      this.alpha -= 0.02;
    }

    this.x += this.vx;
    this.y += this.vy;

    if (this.x <= 0 || this.x >= canvas.width - 1) this.vx *= -0.9;
    if (this.y < 0 || this.y > canvas.height) this.vy *= -0.9;

    this.size += this.sizeDirection * 0.1;
    if (this.size > 3 || this.size < 0.5) this.sizeDirection *= -1;

    // Keep hue oscillating within yellow range so dots stay yellow
    this.hue += 0.2;
    if (this.hue > 65) this.hue = 50;

    if (frameCount % 2 === 0 && (Math.abs(this.vx) > 0.1 || Math.abs(this.vy) > 0.1)) {
      this.trail.push({ x: this.x, y: this.y, hue: this.hue, alpha: this.alpha });
      if (this.trail.length > 15) this.trail.shift();
    }
  }

  draw(ctx) {
    const glowColor = `hsl(${this.hue}, 90%, 60%)`;
    const gradient = ctx.createRadialGradient(this.x, this.y, 0, this.x, this.y, this.size);
    gradient.addColorStop(0, `hsla(${this.hue}, 100%, 70%, ${Math.max(this.alpha, 0)})`);
    gradient.addColorStop(1, `hsla(${this.hue}, 80%, 40%, 0)`);

    ctx.fillStyle = gradient;
    ctx.shadowBlur = 20;
    ctx.shadowColor = glowColor;
    ctx.beginPath();
    ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
    ctx.fill();
    ctx.shadowBlur = 0;

    if (this.trail.length > 1) {
      ctx.beginPath();
      ctx.lineWidth = 1.5;
      for (let i = 0; i < this.trail.length - 1; i++) {
        const { x: x1, y: y1, hue: h1, alpha: a1 } = this.trail[i];
        const { x: x2, y: y2 } = this.trail[i + 1];
        ctx.strokeStyle = `hsla(${h1}, 100%, 60%, ${Math.max(a1, 0)})`;
        ctx.moveTo(x1, y1);
        ctx.lineTo(x2, y2);
      }
      ctx.stroke();
    }
  }

  isDead() {
    return this.isFirework && this.alpha <= 0;
  }
}

  class DustParticle {
  constructor() {
    Object.assign(this, {
      x: Math.random() * canvas.width,
      y: Math.random() * canvas.height,
      size: Math.random() * 1.2 + 0.3,
      // Keep dust glow within a yellow band
      hue: 50 + Math.random() * 15,
      vx: (Math.random() - 0.5) * 0.05,
      vy: (Math.random() - 0.5) * 0.05
    });
  }

  update() {
    this.x = (this.x + this.vx + canvas.width) % canvas.width;
    this.y = (this.y + this.vy + canvas.height) % canvas.height;
    // Gentle drift but stay in yellow
    this.hue += 0.05;
    if (this.hue > 65) this.hue = 50;
  }

  draw(ctx) {
    ctx.fillStyle = `hsla(${this.hue}, 50%, 70%, 0.25)`;
    ctx.beginPath();
    ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
    ctx.fill();
  }
}

class Ripple {
  constructor(x, y, hue = 90, maxRadius = 30) {
    Object.assign(this, { x, y, radius: 0, maxRadius, alpha: 0.5, hue });
  }

  update() {
    this.radius += 1.5;
    this.alpha -= 0.01;
    this.hue = (this.hue + 5) % 360;
  }

  draw(ctx) {
    ctx.strokeStyle = `hsla(${this.hue}, 90%, 60%, ${this.alpha})`;
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
    ctx.stroke();
  }

  isDone() {
    return this.alpha <= 0;
  }
}

function createParticles() {
  particles.length = 0;
  dustParticles.length = 0;

  const numParticles = adjustParticleCount();
  for (let i = 0; i < numParticles; i++) {
    particles.push(new Particle(Math.random() * canvas.width, Math.random() * canvas.height));
  }
  for (let i = 0; i < 80; i++) {
    dustParticles.push(new DustParticle());
  }
}

  function resizeCanvas() {
  // Size canvas to the container section (.landingpage or .bg)
  if (!containerEl || containerEl.offsetParent === null) {
    canvas.width = 0;
    canvas.height = 0;
    return;
  }
  const rect = containerEl.getBoundingClientRect();
  canvas.width = Math.max(0, Math.floor(rect.width));
  canvas.height = Math.max(0, Math.floor(rect.height));
  createParticles();
}

  function drawBackground() {
  const gradient = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
  gradient.addColorStop(0, "#0A1B0A");
  gradient.addColorStop(1, "#133D1C");
  ctx.fillStyle = gradient;
  ctx.fillRect(0, 0, canvas.width, canvas.height);
}

  function connectParticles() {
  const gridSize = 120;
  const grid = new Map();

  particles.forEach((p) => {
    const key = `${Math.floor(p.x / gridSize)},${Math.floor(p.y / gridSize)}`;
    if (!grid.has(key)) grid.set(key, []);
    grid.get(key).push(p);
  });

  ctx.lineWidth = 1.2;
  particles.forEach((p) => {
    const gridX = Math.floor(p.x / gridSize);
    const gridY = Math.floor(p.y / gridSize);

    for (let dx = -1; dx <= 1; dx++) {
      for (let dy = -1; dy <= 1; dy++) {
        const key = `${gridX + dx},${gridY + dy}`;
        if (grid.has(key)) {
          grid.get(key).forEach((neighbor) => {
            if (neighbor !== p) {
              const diffX = neighbor.x - p.x;
              const diffY = neighbor.y - p.y;
              const dist = diffX * diffX + diffY * diffY;
              if (dist < 10000) {
                ctx.strokeStyle = `rgba(0, 255, 100, ${1 - Math.sqrt(dist) / 100})`;
                ctx.beginPath();
                ctx.moveTo(p.x, p.y);
                ctx.lineTo(neighbor.x, neighbor.y);
                ctx.stroke();
              }
            }
          });
        }
      }
    }
  });
}

  function animate() {
  drawBackground();

  [dustParticles, particles, ripples, techRipples, fireworkParticles].forEach((arr) => {
    for (let i = arr.length - 1; i >= 0; i--) {
      const obj = arr[i];
      obj.update(mouse);
      obj.draw(ctx);
      if (obj.isDone?.() || obj.isDead?.()) arr.splice(i, 1);
    }
  });

  connectParticles();
  frameCount++;
  requestAnimationFrame(animate);
}

  canvas.addEventListener("mousemove", (e) => {
  const rect = canvas.getBoundingClientRect();
  mouse.set({ x: e.clientX - rect.left, y: e.clientY - rect.top });
  techRipples.push(new Ripple(mouse.x, mouse.y));
  autoDrift = false;
});

  canvas.addEventListener("mouseleave", () => {
  mouse.reset();
  autoDrift = true;
});

  canvas.addEventListener("click", (e) => {
  const rect = canvas.getBoundingClientRect();
  const clickX = e.clientX - rect.left;
  const clickY = e.clientY - rect.top;

  ripples.push(new Ripple(clickX, clickY, 100, 60));

  for (let i = 0; i < 10; i++) {
    const angle = Math.random() * Math.PI * 2;
    const speed = Math.random() * 2 + 1;
    const particle = new Particle(clickX, clickY, true);
    particle.vx = Math.cos(angle) * speed;
    particle.vy = Math.sin(angle) * speed;
    fireworkParticles.push(particle);
  }
});

  window.addEventListener("resize", resizeCanvas);
  resizeCanvas();
  animate();
})();

