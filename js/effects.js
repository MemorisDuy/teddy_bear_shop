/**
 * Effects JavaScript - Hiệu ứng đặc biệt cho Teddy Bear Shop ✨
 */

$(document).ready(function() {
    initAllEffects();
});

// ===== INITIALIZE ALL EFFECTS =====
function initAllEffects() {
    initFloatingHearts();
    initParticleSystem();
    initMagicalCursor();
    initScrollReveal();
    initTypewriterEffect();
    initGlitterEffect();
    initRainbowText();
    initBouncyButtons();
    initWaveAnimation();
    initStarField();
    
    console.log('✨ All effects initialized!');
}

// ===== FLOATING HEARTS =====
function initFloatingHearts() {
    function createFloatingHeart() {
        const hearts = ['💕', '💖', '💗', '💝', '🐻', '🧸'];
        const heart = hearts[Math.floor(Math.random() * hearts.length)];
        
        const $heart = $('<div class="floating-heart"></div>');
        $heart.text(heart);
        $heart.css({
            position: 'fixed',
            left: Math.random() * window.innerWidth + 'px',
            bottom: '-50px',
            fontSize: (Math.random() * 20 + 15) + 'px',
            opacity: Math.random() * 0.5 + 0.3,
            zIndex: 1,
            pointerEvents: 'none',
            animation: `floatUp ${Math.random() * 3 + 4}s linear forwards`
        });
        
        $('body').append($heart);
        
        // Remove after animation
        setTimeout(() => $heart.remove(), 7000);
    }
    
    // Create hearts periodically
    // setInterval(createFloatingHeart, 3000);
    
    // Create hearts on click
    $(document).on('click', function(e) {
        if (Math.random() > 0.7) { // 30% chance
            createClickHeart(e.pageX, e.pageY);
        }
    });
}

function createClickHeart(x, y) {
    const hearts = ['💕', '💖', '✨', '⭐'];
    const heart = hearts[Math.floor(Math.random() * hearts.length)];
    
    const $heart = $('<div class="click-heart"></div>');
    $heart.text(heart);
    $heart.css({
        position: 'absolute',
        left: x + 'px',
        top: y + 'px',
        fontSize: '24px',
        zIndex: 9999,
        pointerEvents: 'none',
        animation: 'clickHeartAnimation 1s ease-out forwards'
    });
    
    $('body').append($heart);
    setTimeout(() => $heart.remove(), 1000);
}

// ===== PARTICLE SYSTEM =====
function initParticleSystem() {
    const canvas = $('<canvas id="particleCanvas"></canvas>');
    canvas.css({
        position: 'fixed',
        top: 0,
        left: 0,
        width: '100%',
        height: '100%',
        pointerEvents: 'none',
        zIndex: -1,
        opacity: 0.3
    });
    
    $('body').prepend(canvas);
    
    const c = document.getElementById('particleCanvas');
    const ctx = c.getContext('2d');
    
    function resizeCanvas() {
        c.width = window.innerWidth;
        c.height = window.innerHeight;
    }
    
    resizeCanvas();
    $(window).resize(resizeCanvas);
    
    const particles = [];
    const particleCount = 50;
    
    class Particle {
        constructor() {
            this.x = Math.random() * c.width;
            this.y = Math.random() * c.height;
            this.size = Math.random() * 3 + 1;
            this.speedX = Math.random() * 1 - 0.5;
            this.speedY = Math.random() * 1 - 0.5;
            this.color = this.getRandomColor();
            this.opacity = Math.random() * 0.5 + 0.2;
        }
        
        getRandomColor() {
            const colors = ['#ff6b6b', '#ff8e8e', '#ffa8a8', '#ffc9c9', '#ffe0e0'];
            return colors[Math.floor(Math.random() * colors.length)];
        }
        
        update() {
            this.x += this.speedX;
            this.y += this.speedY;
            
            if (this.x > c.width) this.x = 0;
            if (this.x < 0) this.x = c.width;
            if (this.y > c.height) this.y = 0;
            if (this.y < 0) this.y = c.height;
        }
        
        draw() {
            ctx.save();
            ctx.globalAlpha = this.opacity;
            ctx.fillStyle = this.color;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fill();
            ctx.restore();
        }
    }
    
    // Create particles
    for (let i = 0; i < particleCount; i++) {
        particles.push(new Particle());
    }
    
    function animateParticles() {
        ctx.clearRect(0, 0, c.width, c.height);
        
        particles.forEach(particle => {
            particle.update();
            particle.draw();
        });
        
        requestAnimationFrame(animateParticles);
    }
    
    animateParticles();
}

// ===== MAGICAL CURSOR =====
function initMagicalCursor() {
    let mouseX = 0, mouseY = 0;
    let trail = [];
    
    $(document).mousemove(function(e) {
        mouseX = e.pageX;
        mouseY = e.pageY;
        
        // Add to trail
        trail.push({ x: mouseX, y: mouseY, time: Date.now() });
        
        // Remove old trail points
        trail = trail.filter(point => Date.now() - point.time < 500);
        
        createCursorTrail();
    });
    
    function createCursorTrail() {
        $('.cursor-trail').remove();
        
        trail.forEach((point, index) => {
            const age = Date.now() - point.time;
            const opacity = 1 - (age / 500);
            const size = 8 - (age / 500) * 6;
            
            if (opacity > 0 && index % 3 === 0) { // Every 3rd point
                const $trail = $('<div class="cursor-trail"></div>');
                $trail.css({
                    position: 'absolute',
                    left: point.x + 'px',
                    top: point.y + 'px',
                    width: size + 'px',
                    height: size + 'px',
                    background: 'radial-gradient(circle, #ff6b6b, transparent)',
                    borderRadius: '50%',
                    opacity: opacity,
                    pointerEvents: 'none',
                    zIndex: 9998,
                    transform: 'translate(-50%, -50%)'
                });
                
                $('body').append($trail);
                
                setTimeout(() => $trail.remove(), 100);
            }
        });
    }
}

// ===== SCROLL REVEAL =====
function initScrollReveal() {
    function revealOnScroll() {
        $('.reveal-on-scroll').each(function() {
            const elementTop = $(this).offset().top;
            const elementBottom = elementTop + $(this).outerHeight();
            const viewportTop = $(window).scrollTop();
            const viewportBottom = viewportTop + $(window).height();
            
            if (elementBottom > viewportTop && elementTop < viewportBottom) {
                $(this).addClass('revealed');
            }
        });
    }
    
    $(window).on('scroll', throttle(revealOnScroll, 100));
    revealOnScroll(); // Initial check
}

// ===== TYPEWRITER EFFECT =====
function initTypewriterEffect() {
    $('.typewriter').each(function() {
        const $this = $(this);
        const text = $this.text();
        const speed = $this.data('speed') || 100;
        
        $this.text('');
        
        let i = 0;
        function typeWriter() {
            if (i < text.length) {
                $this.text($this.text() + text.charAt(i));
                i++;
                setTimeout(typeWriter, speed);
            }
        }
        
        // Start when element is visible
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    typeWriter();
                    observer.unobserve(entry.target);
                }
            });
        });
        
        observer.observe(this);
    });
}

// ===== GLITTER EFFECT =====
function initGlitterEffect() {
    $('.glitter-text').each(function() {
        const $this = $(this);
        
        function addGlitter() {
            const rect = this.getBoundingClientRect();
            const glitter = $('<span class="glitter-particle">✨</span>');
            
            glitter.css({
                position: 'absolute',
                left: Math.random() * rect.width + 'px',
                top: Math.random() * rect.height + 'px',
                fontSize: Math.random() * 10 + 5 + 'px',
                opacity: Math.random() * 0.8 + 0.2,
                pointerEvents: 'none',
                animation: 'glitterFade 2s ease-out forwards'
            });
            
            $this.css('position', 'relative').append(glitter);
            
            setTimeout(() => glitter.remove(), 2000);
        }
        
        setInterval(addGlitter.bind(this), 1000);
    });
}

// ===== RAINBOW TEXT =====
function initRainbowText() {
    $('.rainbow-text').each(function() {
        const text = $(this).text();
        const colors = ['#ff6b6b', '#ff8e8e', '#ffa8a8', '#ff20a0', '#c44569', '#f8b500'];
        
        let html = '';
        for (let i = 0; i < text.length; i++) {
            const char = text[i];
            const color = colors[i % colors.length];
            html += `<span style="color: ${color}; animation: rainbowPulse ${Math.random() * 2 + 1}s infinite alternate;">${char}</span>`;
        }
        
        $(this).html(html);
    });
}

// ===== BOUNCY BUTTONS =====
function initBouncyButtons() {
    $('.bouncy-btn, .cute-btn').hover(
        function() {
            $(this).addClass('bounce-hover');
        },
        function() {
            $(this).removeClass('bounce-hover');
        }
    );
    
    $('.bouncy-btn, .cute-btn').on('click', function() {
        $(this).addClass('bounce-click');
        setTimeout(() => $(this).removeClass('bounce-click'), 300);
    });
}

// ===== WAVE ANIMATION =====
function initWaveAnimation() {
    $('.wave-text').each(function() {
        const text = $(this).text();
        let html = '';
        
        for (let i = 0; i < text.length; i++) {
            const char = text[i] === ' ' ? '&nbsp;' : text[i];
            html += `<span class="wave-char" style="animation-delay: ${i * 0.1}s">${char}</span>`;
        }
        
        $(this).html(html);
    });
}

// ===== STAR FIELD =====
function initStarField() {
    if ($('.star-field').length === 0) return;
    
    const starCount = 100;
    const $starField = $('.star-field');
    
    for (let i = 0; i < starCount; i++) {
        const $star = $('<div class="star"></div>');
        $star.css({
            position: 'absolute',
            left: Math.random() * 100 + '%',
            top: Math.random() * 100 + '%',
            width: Math.random() * 3 + 1 + 'px',
            height: Math.random() * 3 + 1 + 'px',
            background: '#fff',
            borderRadius: '50%',
            opacity: Math.random() * 0.8 + 0.2,
            animation: `twinkle ${Math.random() * 3 + 2}s infinite alternate`
        });
        
        $starField.append($star);
    }
}

// ===== PRODUCT HOVER EFFECTS =====
function initProductHoverEffects() {
    $('.product-card').hover(
        function() {
            $(this).find('.product-image img').addClass('zoom-in');
            createProductGlow(this);
        },
        function() {
            $(this).find('.product-image img').removeClass('zoom-in');
            $(this).find('.product-glow').remove();
        }
    );
}

function createProductGlow(element) {
    const $glow = $('<div class="product-glow"></div>');
    $glow.css({
        position: 'absolute',
        top: '-5px',
        left: '-5px',
        right: '-5px',
        bottom: '-5px',
        background: 'linear-gradient(45deg, #ff6b6b, #ff8e8e, #ffa8a8)',
        borderRadius: '25px',
        zIndex: -1,
        opacity: 0.3,
        filter: 'blur(10px)',
        animation: 'pulseGlow 2s infinite'
    });
    
    $(element).css('position', 'relative').append($glow);
}

// ===== CATEGORY HOVER EFFECTS =====
function initCategoryHoverEffects() {
    $('.category-card').hover(
        function() {
            createCategoryRipple(this);
        },
        function() {
            $(this).find('.category-ripple').remove();
        }
    );
}

function createCategoryRipple(element) {
    const $ripple = $('<div class="category-ripple"></div>');
    $ripple.css({
        position: 'absolute',
        top: '50%',
        left: '50%',
        width: '0',
        height: '0',
        borderRadius: '50%',
        background: 'rgba(255, 255, 255, 0.4)',
        transform: 'translate(-50%, -50%)',
        animation: 'rippleExpand 0.6s ease-out'
    });
    
    $(element).css('position', 'relative').append($ripple);
}

// ===== LOADING ANIMATIONS =====
function initLoadingAnimations() {
    // Cute loading spinner
    $('.cute-loading').each(function() {
        const $this = $(this);
        const emojis = ['🐻', '🧸', '💕', '✨'];
        let currentEmoji = 0;
        
        setInterval(() => {
            $this.text(emojis[currentEmoji]);
            currentEmoji = (currentEmoji + 1) % emojis.length;
        }, 500);
    });
}

// ===== TEXT EFFECTS =====
function initTextEffects() {
    // Gradient text animation
    $('.gradient-text-animated').each(function() {
        $(this).css({
            background: 'linear-gradient(45deg, #ff6b6b, #ff8e8e, #ffa8a8, #ff6b6b)',
            backgroundSize: '300% 300%',
            animation: 'gradientShift 3s ease infinite'
        });
    });
    
    // Shadow text effect
    $('.shadow-text').each(function() {
        const text = $(this).text();
        $(this).attr('data-text', text);
    });
}

// ===== FORM EFFECTS =====
function initFormEffects() {
    // Focus effects
    $('.form-control').focus(function() {
        $(this).parent().addClass('form-group-focused');
        createFocusRipple(this);
    });
    
    $('.form-control').blur(function() {
        $(this).parent().removeClass('form-group-focused');
    });
}

function createFocusRipple(element) {
    const $ripple = $('<div class="focus-ripple"></div>');
    $ripple.css({
        position: 'absolute',
        top: '0',
        left: '0',
        right: '0',
        bottom: '0',
        border: '2px solid #ff6b6b',
        borderRadius: 'inherit',
        opacity: 0,
        animation: 'focusRipple 0.3s ease-out'
    });
    
    $(element).parent().css('position', 'relative').append($ripple);
    setTimeout(() => $ripple.remove(), 300);
}

// ===== SCROLL EFFECTS =====
function initScrollEffects() {
    let lastScrollTop = 0;
    
    $(window).scroll(function() {
        const scrollTop = $(this).scrollTop();
        
        // Parallax effect
        $('.parallax-element').each(function() {
            const speed = $(this).data('speed') || 0.5;
            const yPos = -(scrollTop * speed);
            $(this).css('transform', `translateY(${yPos}px)`);
        });
        
        // Header hide/show
        if (scrollTop > lastScrollTop && scrollTop > 100) {
            $('.main-header').addClass('header-hidden');
        } else {
            $('.main-header').removeClass('header-hidden');
        }
        
        lastScrollTop = scrollTop;
        
        // Progress indicator
        updateScrollProgress();
    });
}

function updateScrollProgress() {
    const scrollTop = $(window).scrollTop();
    const docHeight = $(document).height() - $(window).height();
    const scrollPercent = (scrollTop / docHeight) * 100;
    
    $('.scroll-progress').css('width', scrollPercent + '%');
}

// ===== INTERACTIVE ELEMENTS =====
function initInteractiveElements() {
    // Magnetic buttons
    $('.magnetic-btn').mousemove(function(e) {
        const rect = this.getBoundingClientRect();
        const x = e.clientX - rect.left - rect.width / 2;
        const y = e.clientY - rect.top - rect.height / 2;
        
        $(this).css('transform', `translate(${x * 0.1}px, ${y * 0.1}px)`);
    });
    
    $('.magnetic-btn').mouseleave(function() {
        $(this).css('transform', 'translate(0, 0)');
    });
    
    // Tilt effect
    $('.tilt-effect').mousemove(function(e) {
        const rect = this.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        
        const rotateX = (y - centerY) / 10;
        const rotateY = (centerX - x) / 10;
        
        $(this).css('transform', `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`);
    });
    
    $('.tilt-effect').mouseleave(function() {
        $(this).css('transform', 'perspective(1000px) rotateX(0) rotateY(0)');
    });
}

// ===== CONFETTI EFFECT =====
function createConfetti() {
    const confettiCount = 50;
    const colors = ['#ff6b6b', '#ff8e8e', '#ffa8a8', '#ff20a0', '#ffd93d', '#6bcf7f'];
    
    for (let i = 0; i < confettiCount; i++) {
        const confetti = $('<div class="confetti"></div>');
        confetti.css({
            position: 'fixed',
            left: Math.random() * window.innerWidth + 'px',
            top: '-10px',
            width: Math.random() * 10 + 5 + 'px',
            height: Math.random() * 10 + 5 + 'px',
            background: colors[Math.floor(Math.random() * colors.length)],
            zIndex: 9999,
            pointerEvents: 'none',
            animation: `confettiFall ${Math.random() * 3 + 2}s linear forwards`
        });
        
        $('body').append(confetti);
        setTimeout(() => confetti.remove(), 5000);
    }
}

// ===== FIREWORKS EFFECT =====
function createFireworks(x, y) {
    const fireworkCount = 20;
    const colors = ['#ff6b6b', '#ff8e8e', '#ffa8a8', '#ff20a0', '#ffd93d'];
    
    for (let i = 0; i < fireworkCount; i++) {
        const spark = $('<div class="firework-spark"></div>');
        const angle = (Math.PI * 2 * i) / fireworkCount;
        const velocity = Math.random() * 100 + 50;
        
        spark.css({
            position: 'absolute',
            left: x + 'px',
            top: y + 'px',
            width: '4px',
            height: '4px',
            background: colors[Math.floor(Math.random() * colors.length)],
            borderRadius: '50%',
            zIndex: 9999,
            pointerEvents: 'none'
        });
        
        $('body').append(spark);
        
        // Animate spark
        spark.animate({
            left: x + Math.cos(angle) * velocity + 'px',
            top: y + Math.sin(angle) * velocity + 'px',
            opacity: 0
        }, 1000, function() {
            $(this).remove();
        });
    }
}

// ===== SPECIAL EFFECTS TRIGGERS =====
function initSpecialTriggers() {
    // Confetti on successful actions
    $(document).on('order-success', function() {
        createConfetti();
    });
    
    // Fireworks on special occasions
    $(document).on('achievement-unlocked', function(e, data) {
        createFireworks(window.innerWidth / 2, window.innerHeight / 2);
    });
    
    // Special effects for milestones
    $(document).on('milestone-reached', function(e, milestone) {
        switch (milestone) {
            case 'first-purchase':
                createConfetti();
                break;
            case 'loyal-customer':
                createFireworks(window.innerWidth / 2, window.innerHeight / 3);
                break;
        }
    });
}

// ===== PERFORMANCE OPTIMIZATIONS =====
function optimizeEffects() {
    // Reduce effects on mobile
    if (window.innerWidth < 768) {
        $('.floating-hearts .heart').each(function(index) {
            if (index % 2 === 0) $(this).remove();
        });
    }
    
    // Pause effects when tab is not visible
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            $('*').css('animation-play-state', 'paused');
        } else {
            $('*').css('animation-play-state', 'running');
        }
    });
    
    // Use requestAnimationFrame for smooth animations
    function smoothAnimationLoop() {
        // Update any smooth animations here
        requestAnimationFrame(smoothAnimationLoop);
    }
    
    requestAnimationFrame(smoothAnimationLoop);
}

// ===== CUSTOM CSS ANIMATIONS =====
function addCustomAnimations() {
    const styles = `
        <style>
        @keyframes floatUp {
            to {
                transform: translateY(-100vh);
                opacity: 0;
            }
        }
        
        @keyframes clickHeartAnimation {
            0% {
                transform: scale(0) rotate(0deg);
                opacity: 1;
            }
            50% {
                transform: scale(1.2) rotate(180deg);
                opacity: 0.8;
            }
            100% {
                transform: scale(0) rotate(360deg);
                opacity: 0;
            }
        }
        
        @keyframes glitterFade {
            0% {
                transform: scale(0) rotate(0deg);
                opacity: 1;
            }
            50% {
                transform: scale(1) rotate(180deg);
                opacity: 0.8;
            }
            100% {
                transform: scale(0) rotate(360deg);
                opacity: 0;
            }
        }
        
        @keyframes rainbowPulse {
            0% { transform: scale(1); }
            100% { transform: scale(1.1); }
        }
        
        @keyframes twinkle {
            0% { opacity: 0.2; transform: scale(1); }
            100% { opacity: 1; transform: scale(1.2); }
        }
        
        @keyframes pulseGlow {
            0% { opacity: 0.3; }
            50% { opacity: 0.6; }
            100% { opacity: 0.3; }
        }
        
        @keyframes rippleExpand {
            0% {
                width: 0;
                height: 0;
                opacity: 1;
            }
            100% {
                width: 200px;
                height: 200px;
                opacity: 0;
            }
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        @keyframes focusRipple {
            0% {
                opacity: 0;
                transform: scale(0.8);
            }
            50% {
                opacity: 1;
                transform: scale(1.05);
            }
            100% {
                opacity: 0;
                transform: scale(1);
            }
        }
        
        @keyframes confettiFall {
            0% {
                transform: translateY(-100vh) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) rotate(720deg);
                opacity: 0;
            }
        }
        
        .wave-char {
            display: inline-block;
            animation: wave 2s ease-in-out infinite;
        }
        
        @keyframes wave {
            0%, 60%, 100% {
                transform: initial;
            }
            30% {
                transform: translateY(-15px);
            }
        }
        
        .bounce-hover {
            animation: bounce 0.6s;
        }
        
        .bounce-click {
            animation: bounceClick 0.3s;
        }
        
        @keyframes bounceClick {
            0% { transform: scale(1); }
            50% { transform: scale(0.95); }
            100% { transform: scale(1); }
        }
        
        .reveal-on-scroll {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.6s ease;
        }
        
        .reveal-on-scroll.revealed {
            opacity: 1;
            transform: translateY(0);
        }
        
        .cart-item-updated {
            animation: highlightUpdate 1s ease;
        }
        
        @keyframes highlightUpdate {
            0% { background: rgba(255, 107, 107, 0.1); }
            50% { background: rgba(255, 107, 107, 0.3); }
            100% { background: transparent; }
        }
        
        .updating {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .removing {
            animation: fadeOutScale 0.4s ease forwards;
        }
        
        @keyframes fadeOutScale {
            0% {
                opacity: 1;
                transform: scale(1);
            }
            100% {
                opacity: 0;
                transform: scale(0.8);
            }
        }
        </style>
    `;
    
    $('head').append(styles);
}

// ===== UTILITY FUNCTIONS =====
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    }
}

function debounce(func, wait, immediate) {
    let timeout;
    return function() {
        const context = this, args = arguments;
        const later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

// ===== INITIALIZE ALL EFFECTS =====
$(document).ready(function() {
    addCustomAnimations();
    initProductHoverEffects();
    initCategoryHoverEffects();
    initLoadingAnimations();
    initTextEffects();
    initFormEffects();
    initScrollEffects();
    initInteractiveElements();
    initSpecialTriggers();
    optimizeEffects();
});

// ===== EXPORT FUNCTIONS =====
window.TeddyEffects = {
    createConfetti,
    createFireworks,
    createClickHeart,
    createFloatingHeart,
    updateScrollProgress
};

console.log('✨ Effects JavaScript loaded successfully!');