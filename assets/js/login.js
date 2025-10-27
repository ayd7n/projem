document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('sky-canvas');
    const ctx = canvas.getContext('2d');
    const loginContainer = document.querySelector('.login-container');

    let width = canvas.width = window.innerWidth;
    let height = canvas.height = window.innerHeight;

    window.addEventListener('resize', () => {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
        initStars(); // Re-initialize stars on resize
    });

    // --- 3D Tilt Effect ---
    loginContainer.addEventListener('mousemove', (e) => {
        const { left, top, width, height } = loginContainer.getBoundingClientRect();
        const x = e.clientX - left - width / 2;
        const y = e.clientY - top - height / 2;

        const rotateX = (y / height) * -10; // Max rotation 5 degrees
        const rotateY = (x / width) * 10;  // Max rotation 5 degrees

        loginContainer.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(0)`;
    });

    loginContainer.addEventListener('mouseleave', () => {
        loginContainer.style.transform = 'rotateX(0) rotateY(0) translateZ(0)';
    });

    // --- Starry Sky Animation ---
    let stars = [];
    let shootingStars = [];
    const numStars = 200;

    function initStars() {
        stars = [];
        for (let i = 0; i < numStars; i++) {
            stars.push({
                x: Math.random() * width,
                y: Math.random() * height,
                radius: Math.random() * 1.2 + 0.3, // More varied sizes
                alpha: Math.random() * 0.7 + 0.3, // More varied opacities
                twinkleSpeed: Math.random() * 0.01 + 0.005
            });
        }
    }

    function createShootingStar() {
        shootingStars.push({
            x: Math.random() * width,
            y: Math.random() * height * 0.2, // Start in the top 20%
            len: Math.random() * 80 + 10,
            speed: Math.random() * 8 + 5,
            alpha: 1,
            angle: Math.PI / 4 // 45-degree angle
        });
    }

    function draw() {
        ctx.clearRect(0, 0, width, height);
        
        // Draw stars
        stars.forEach(star => {
            ctx.save();
            star.alpha += star.twinkleSpeed;
            if (star.alpha > 1) {
                star.alpha = 1;
                star.twinkleSpeed *= -1;
            } else if (star.alpha < 0.3) {
                star.alpha = 0.3;
                star.twinkleSpeed *= -1;
            }
            ctx.globalAlpha = star.alpha;
            ctx.fillStyle = 'white';
            ctx.beginPath();
            ctx.arc(star.x, star.y, star.radius, 0, Math.PI * 2);
            ctx.fill();
            ctx.restore();
        });

        // Draw and update shooting stars
        shootingStars.forEach((ss, index) => {
            ctx.save();
            ctx.globalAlpha = ss.alpha;
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.7)';
            ctx.lineWidth = 1.5;
            ctx.beginPath();
            ctx.moveTo(ss.x, ss.y);
            ctx.lineTo(ss.x + ss.len * Math.cos(ss.angle), ss.y + ss.len * Math.sin(ss.angle));
            ctx.stroke();
            ctx.restore();

            ss.x += ss.speed * Math.cos(ss.angle);
            ss.y += ss.speed * Math.sin(ss.angle);
            ss.alpha -= 0.02;

            if (ss.alpha <= 0) {
                shootingStars.splice(index, 1);
            }
        });
    }

    function animate() {
        draw();
        requestAnimationFrame(animate);
    }

    // Randomly create shooting stars
    setInterval(createShootingStar, 3000); // Every 3 seconds

    initStars();
    animate();
});
