import * as THREE from 'three';
import gsap from 'gsap';
import ScrollTrigger from 'gsap/ScrollTrigger';
import { animate } from '@motionone/dom';

gsap.registerPlugin(ScrollTrigger);

function prefersReducedMotion() {
    return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function initFloatingCards() {
    const els = document.querySelectorAll('[data-float]');
    if (!els.length || prefersReducedMotion()) return;

    els.forEach((el, i) => {
        const amp = Number(el.dataset.floatAmp || 10);
        const dur = Number(el.dataset.floatDur || 4.5) + (i % 3) * 0.6;
        const rot = Number(el.dataset.floatRot || 2.5);

        gsap.to(el, {
            y: `+=${amp}`,
            rotateZ: rot,
            duration: dur,
            ease: 'sine.inOut',
            yoyo: true,
            repeat: -1,
            delay: i * 0.15,
        });

        gsap.to(el, {
            x: `+=${Math.round(amp * 0.6)}`,
            duration: dur + 1.2,
            ease: 'sine.inOut',
            yoyo: true,
            repeat: -1,
            delay: i * 0.12,
        });
    });
}

function initRevealAnimations() {
    const items = document.querySelectorAll('[data-reveal]');
    if (!items.length) return;

    // Group-stagger reveals for smoother, more “designed” motion
    const groups = new Map();
    items.forEach((el) => {
        const key = el.closest('[data-reveal-group]') || el.parentElement || document.body;
        const id = key?.getAttribute?.('data-reveal-group') || (key === document.body ? 'body' : 'group');
        const k = `${id}-${items.length}-${key?.tagName || 'X'}`;
        const arr = groups.get(k) || [];
        arr.push(el);
        groups.set(k, arr);
    });

    groups.forEach((els) => {
        gsap.fromTo(
            els,
            { y: 18, opacity: 0 },
            {
                y: 0,
                opacity: 1,
                duration: 0.75,
                ease: 'power2.out',
                stagger: 0.07,
                scrollTrigger: {
                    trigger: els[0],
                    start: 'top 85%',
                },
            },
        );
    });
}

function initMotionMicrointeractions() {
    const buttons = document.querySelectorAll('[data-motion-btn]');
    buttons.forEach((btn) => {
        btn.addEventListener('mouseenter', () => {
            animate(btn, { transform: ['translateY(0px) scale(1)', 'translateY(-2px) scale(1.02)'] }, { duration: 0.18 });
        });
        btn.addEventListener('mouseleave', () => {
            animate(btn, { transform: ['translateY(-2px) scale(1.02)', 'translateY(0px) scale(1)'] }, { duration: 0.18 });
        });
        btn.addEventListener('mousedown', () => {
            animate(btn, { transform: ['translateY(-2px) scale(1.02)', 'translateY(0px) scale(0.99)'] }, { duration: 0.06 });
        });
        btn.addEventListener('mouseup', () => {
            animate(btn, { transform: ['translateY(0px) scale(0.99)', 'translateY(-2px) scale(1.02)'] }, { duration: 0.08 });
        });
    });
}

function initSmoothAnchorScroll() {
    document.querySelectorAll('a[href^="#"]').forEach((a) => {
        a.addEventListener('click', (e) => {
            const id = a.getAttribute('href');
            if (!id || id === '#') return;
            const el = document.querySelector(id);
            if (!el) return;
            e.preventDefault();
            el.scrollIntoView({ behavior: prefersReducedMotion() ? 'auto' : 'smooth', block: 'start' });
        });
    });
}

function initHeroThree() {
    const container = document.querySelector('[data-hero-3d]');
    if (!container) return;

    // Performance guardrails
    const isSmall = window.matchMedia && window.matchMedia('(max-width: 640px)').matches;
    const reduce = prefersReducedMotion();

    const canvas = document.createElement('canvas');
    canvas.className = 'w-full h-full';
    container.appendChild(canvas);

    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true, powerPreference: 'high-performance' });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, isSmall ? 1.35 : 2));

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(40, 1, 0.1, 100);
    camera.position.set(0.2, 0.9, 5.0);

    const group = new THREE.Group();
    scene.add(group);

    // Lighting (enterprise glossy look)
    const key = new THREE.DirectionalLight(0xffffff, 1.2);
    key.position.set(3, 4, 4);
    scene.add(key);

    const fill = new THREE.DirectionalLight(0x93c5fd, 0.55);
    fill.position.set(-3, 1, 2);
    scene.add(fill);

    const rim = new THREE.DirectionalLight(0xa7f3d0, 0.65);
    rim.position.set(-2, 4, -3);
    scene.add(rim);

    const ambient = new THREE.AmbientLight(0xffffff, 0.35);
    scene.add(ambient);

    // Stylized “athlete” (procedural, no external model)
    const bodyMat = new THREE.MeshPhysicalMaterial({
        color: new THREE.Color('#e5e7eb'),
        roughness: 0.25,
        metalness: 0.15,
        clearcoat: 0.6,
        clearcoatRoughness: 0.18,
    });
    const accentMat = new THREE.MeshPhysicalMaterial({
        color: new THREE.Color('#6366f1'),
        roughness: 0.25,
        metalness: 0.22,
        clearcoat: 0.8,
        clearcoatRoughness: 0.16,
    });

    const head = new THREE.Mesh(new THREE.SphereGeometry(0.35, 48, 48), bodyMat);
    head.position.set(0, 1.95, 0);
    group.add(head);

    const torso = new THREE.Mesh(new THREE.CapsuleGeometry(0.45, 1.05, 12, 24), bodyMat);
    torso.position.set(0, 1.1, 0);
    group.add(torso);

    const belt = new THREE.Mesh(new THREE.TorusGeometry(0.46, 0.08, 18, 64), accentMat);
    belt.rotation.x = Math.PI / 2;
    belt.position.set(0, 0.92, 0.02);
    group.add(belt);

    const limbGeo = new THREE.CapsuleGeometry(0.16, 0.9, 10, 18);
    const armL = new THREE.Mesh(limbGeo, accentMat);
    armL.position.set(-0.68, 1.2, 0.05);
    armL.rotation.z = 0.55;
    group.add(armL);

    const armR = new THREE.Mesh(limbGeo, accentMat);
    armR.position.set(0.68, 1.2, -0.05);
    armR.rotation.z = -0.75;
    group.add(armR);

    const legGeo = new THREE.CapsuleGeometry(0.2, 1.2, 10, 20);
    const legL = new THREE.Mesh(legGeo, bodyMat);
    legL.position.set(-0.3, -0.1, 0.1);
    legL.rotation.z = 0.18;
    group.add(legL);

    const legR = new THREE.Mesh(legGeo, bodyMat);
    legR.position.set(0.35, 0.15, -0.05);
    legR.rotation.z = -0.35;
    group.add(legR);

    // “Floor” glow
    const floor = new THREE.Mesh(
        new THREE.CircleGeometry(2.1, 64),
        new THREE.MeshBasicMaterial({ color: 0x60a5fa, transparent: true, opacity: 0.11 }),
    );
    floor.rotation.x = -Math.PI / 2;
    floor.position.y = -1.4;
    group.add(floor);

    // Floating “data orbs”
    const orbMat = new THREE.MeshPhysicalMaterial({
        color: 0x38bdf8,
        roughness: 0.15,
        metalness: 0.05,
        transmission: 0.35,
        transparent: true,
        opacity: 0.75,
        thickness: 1.0,
    });

    const orbs = Array.from({ length: isSmall ? 4 : 7 }, (_, i) => {
        const m = new THREE.Mesh(new THREE.SphereGeometry(0.09 + i * 0.008, 22, 22), orbMat);
        m.position.set((Math.random() - 0.5) * 1.8, 0.4 + Math.random() * 2.0, (Math.random() - 0.5) * 0.9);
        group.add(m);
        return m;
    });

    function resize() {
        const { width, height } = container.getBoundingClientRect();
        renderer.setSize(width, height, false);
        camera.aspect = width / Math.max(1, height);
        camera.updateProjectionMatrix();
    }

    resize();
    const ro = new ResizeObserver(resize);
    ro.observe(container);

    let t = 0;
    let running = true;

    // Pause rendering when hero is offscreen to save CPU/GPU
    const io = new IntersectionObserver(
        (entries) => {
            running = entries.some((e) => e.isIntersecting);
        },
        { threshold: 0.08 },
    );
    io.observe(container);

    function animateFrame() {
        if (!running) {
            requestAnimationFrame(animateFrame);
            return;
        }
        t += 0.01;

        const targetRotY = reduce ? 0.25 : 0.35 + Math.sin(t * 0.7) * 0.10;
        const targetRotX = reduce ? -0.05 : -0.08 + Math.cos(t * 0.8) * 0.05;

        group.rotation.y += (targetRotY - group.rotation.y) * 0.06;
        group.rotation.x += (targetRotX - group.rotation.x) * 0.06;

        orbs.forEach((o, i) => {
            o.position.y += Math.sin(t * 1.2 + i) * 0.0018;
            o.position.x += Math.cos(t * 1.0 + i * 1.3) * 0.0012;
        });

        renderer.render(scene, camera);
        requestAnimationFrame(animateFrame);
    }

    animateFrame();

    // Mouse parallax (subtle)
    if (!reduce) {
        container.addEventListener('pointermove', (e) => {
            const rect = container.getBoundingClientRect();
            const nx = (e.clientX - rect.left) / rect.width - 0.5;
            const ny = (e.clientY - rect.top) / rect.height - 0.5;
            gsap.to(group.rotation, { y: 0.35 + nx * 0.25, x: -0.08 + -ny * 0.18, duration: 0.4, ease: 'power2.out' });
        });
    }
}

function initAntiGravityHero() {
    const root = document.querySelector('[data-hero-parallax]');
    if (!root || prefersReducedMotion()) return;

    // Depth-layer parallax (DOM layers + 3D canvas container)
    const layers = Array.from(root.querySelectorAll('[data-parallax]'));
    if (layers.length) {
        const setters = layers.map((el) => {
            const depth = Number(el.dataset.depth || 0.2);
            const xTo = gsap.quickTo(el, 'x', { duration: 0.6, ease: 'power3.out' });
            const yTo = gsap.quickTo(el, 'y', { duration: 0.6, ease: 'power3.out' });
            const rTo = gsap.quickTo(el, 'rotationZ', { duration: 0.7, ease: 'power3.out' });
            gsap.set(el, { transformStyle: 'preserve-3d' });
            return { depth, xTo, yTo, rTo };
        });

        root.addEventListener('pointermove', (e) => {
            const rect = root.getBoundingClientRect();
            const nx = (e.clientX - rect.left) / rect.width - 0.5;
            const ny = (e.clientY - rect.top) / rect.height - 0.5;

            // tuned for “anti-gravity” feel (small, premium, not twitchy)
            setters.forEach(({ depth, xTo, yTo, rTo }, i) => {
                const dx = nx * 28 * depth;
                const dy = ny * 22 * depth;
                xTo(dx);
                yTo(dy);
                rTo((nx * 2.0 - ny * 1.2) * depth + (i % 2 ? 0.12 : -0.12));
            });
        });

        root.addEventListener('pointerleave', () => {
            setters.forEach(({ xTo, yTo, rTo }) => {
                xTo(0);
                yTo(0);
                rTo(0);
            });
        });
    }

    // Anti-gravity float loop for key foreground elements (cards, etc.)
    const floaters = root.querySelectorAll('[data-anti-grav]');
    floaters.forEach((el, i) => {
        gsap.to(el, {
            y: `-=${10 + i * 2}`,
            duration: 5.4 + i * 0.9,
            ease: 'sine.inOut',
            yoyo: true,
            repeat: -1,
            delay: i * 0.12,
        });
        gsap.to(el, {
            rotateZ: i % 2 ? 0.6 : -0.6,
            duration: 6.6 + i * 0.7,
            ease: 'sine.inOut',
            yoyo: true,
            repeat: -1,
            delay: i * 0.08,
        });
    });
}

function initLanding() {
    if (!document.querySelector('[data-landing]')) return;
    initHeroThree();
    initAntiGravityHero();
    initFloatingCards();
    initRevealAnimations();
    initMotionMicrointeractions();
    initSmoothAnchorScroll();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLanding);
} else {
    initLanding();
}

