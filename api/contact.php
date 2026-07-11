<!DOCTYPE html>
<html lang="sq">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DentCare Pejë — Na Kontaktoni</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
    /* ── Contact-page-only additions ── */
    .contact-hero {
        padding: 120px 48px 60px;
    }

    .contact-grid {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 24px;
        align-items: start;
    }

    @media (max-width: 900px) {
        .contact-grid { grid-template-columns: 1fr; }
    }

    .contact-methods {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-top: 40px;
        margin-bottom: 28px;
    }

    @media (max-width: 768px) {
        .contact-methods { grid-template-columns: 1fr; }
    }

    .contact-method {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 20px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        text-decoration: none;
        color: var(--text);
        transition: transform .2s, box-shadow .2s, border-color .2s;
    }

    .contact-method:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow);
        border-color: var(--green-mid);
    }

    .contact-method-icon {
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        background: var(--green-light);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .contact-method-icon svg {
        width: 18px; height: 18px;
        stroke: var(--green-dark);
        fill: none;
        stroke-width: 2;
    }

    .contact-method .cm-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-soft);
        margin-bottom: 3px;
    }

    .contact-method .cm-value {
        font-size: 14px;
        font-weight: 500;
        color: var(--text);
        line-height: 1.4;
    }

    .social-row {
        display: flex;
        gap: 10px;
        margin-top: 16px;
    }

    .social-btn {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: var(--green-light);
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: background .2s, transform .2s;
    }

    .social-btn:hover { background: var(--green); transform: translateY(-2px); }
    .social-btn svg { width: 16px; height: 16px; stroke: var(--green-dark); fill: none; stroke-width: 2; transition: stroke .2s; }
    .social-btn:hover svg { stroke: white; }
    
    .hero-actions {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
    justify-content: center;
    margin-bottom: 40px;
    }

    .hero-cta.solid {
        background: var(--green);
        color: white;
    }

    .hero-cta.solid:hover {
        background: var(--green-dark);
        color: white;
    }
</style>
</head>
<body>
<nav>
    <a href="index.php" class="nav-logo">
        <div class="nav-logo-icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2C9.5 2 7.5 3.5 6.5 5.5C5.5 4.5 4 4 3 5C1.5 6.5 2 9 3 11C4 13 5 14 5.5 16C6 18 6 20 7 21C7.5 21.5 8.5 22 9 21C9.5 20 9.5 18 10 17C10.5 16 11 15.5 12 15.5C13 15.5 13.5 16 14 17C14.5 18 14.5 20 15 21C15.5 22 16.5 21.5 17 21C18 20 18 18 18.5 16C19 14 20 13 21 11C22 9 22.5 6.5 21 5C20 4 18.5 4.5 17.5 5.5C16.5 3.5 14.5 2 12 2Z"/>
            </svg>
        </div>
        <span class="nav-logo-text">Dent<span>Care</span> Pejë</span>
    </a>

    <button class="menu-toggle" id="menu-toggle" aria-label="Hap menunë">
        <span></span>
        <span></span>
    </button>
    <ul class="nav-links" id="nav-links">
        <li><a href="index.php">Faqja Hyrëse</a></li>
        <li><a href="#lokacioni">Lokacioni</a></li>
    </ul>
</nav>

<section class="hero contact-hero">
    <div class="hero-bg-circle"></div>
    <div class="hero-bg-circle-bottom"></div>
    <div class="hero-tag">Jemi këtu për ju</div>
    <h1>Na <em>Kontaktoni</em></h1>
    <p class="hero-sub">Keni pyetje rreth një shërbimi, urgjencë dentale, apo dëshironi thjesht të na thoni përshëndetje? Na shkruani më poshtë ose na gjeni direkt.</p>
    
    <div class="hero-actions">
    <a href="#kontakt" class="hero-cta solid">
        Shkruani Mesazh
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
    </a>
    <a href="tel:+38344000000" class="hero-cta">
        Na Telefononi
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.38 2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.6a16 16 0 0 0 6.29 6.29l.95-.94a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
    </a>
</div>
</section>

<section class="booking-section" id="kontakt">
    <div class="contact-methods">
        <a href="tel:+38344000000" class="contact-method">
            <div class="contact-method-icon">
                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.38 2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.6a16 16 0 0 0 6.29 6.29l.95-.94a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </div>
            <div>
                <div class="cm-label">Telefononi</div>
                <div class="cm-value">+383 44 000 000</div>
            </div>
        </a>
        <a href="mailto:info@dentcarepeje.com" class="contact-method">
            <div class="contact-method-icon">
                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0a2.25 2.25 0 00-2.25-2.25h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
            </div>
            <div>
                <div class="cm-label">Email</div>
                <div class="cm-value">info@dentcarepeje.com</div>
            </div>
        </a>
        <a href="#lokacioni" class="contact-method">
            <div class="contact-method-icon">
                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div>
                <div class="cm-label">Adresa</div>
                <div class="cm-value">Rr. Mbretëresha Teuta, Pejë</div>
            </div>
        </a>
    </div>

    <div class="contact-grid">
        <div class="form-card">
            <div id="alert-box" class="alert alert-error"></div>

            <div class="form-section">
                <div class="form-section-title">
                    <div class="section-num">1</div>
                    <h3>Të Dhënat Tuaja</h3>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label for="c-name">Emri i Plotë *</label>
                        <input type="text" id="c-name" placeholder="Emri dhe Mbiemri">
                        <span class="field-error" id="c-err-name">Ju lutemi jepni emrin tuaj të plotë</span>
                    </div>
                    <div class="field">
                        <label for="c-phone">Numri i Telefonit</label>
                        <input type="tel" id="c-phone" placeholder="+383 44 ...">
                    </div>
                </div>
                <div class="form-row single" style="margin-top:14px">
                    <div class="field">
                        <label for="c-email">Email Adresa *</label>
                        <input type="email" id="c-email" placeholder="arian@email.com">
                        <span class="field-error" id="c-err-email">Ju lutemi jepni një email të vlefshëm</span>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">
                    <div class="section-num">2</div>
                    <h3>Mesazhi</h3>
                </div>
                <div class="form-row single">
                    <div class="field">
                        <label for="c-subject">Subjekti *</label>
                        <select id="c-subject">
                            <option value="">Zgjidhni një temë...</option>
                            <option>Pyetje e Përgjithshme</option>
                            <option>Urgjencë Dentale</option>
                            <option>Rreth një Termini Ekzistues</option>
                            <option>Faturim & Pagesa</option>
                            <option>Tjetër</option>
                        </select>
                        <span class="field-error" id="c-err-subject">Ju lutemi zgjidhni një temë</span>
                    </div>
                </div>
                <div class="form-row single" style="margin-top:14px">
                    <div class="field">
                        <label for="c-message">Mesazhi Juaj *</label>
                        <textarea id="c-message" placeholder="Si mund t'ju ndihmojmë?" style="min-height:120px"></textarea>
                        <span class="field-error" id="c-err-message">Ju lutemi shkruani një mesazh</span>
                    </div>
                </div>
            </div>

            <div class="submit-area">
                <button class="btn-submit" onclick="submitContact()" id="c-submit-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    <span id="c-btn-text">Dërgo Mesazhin</span>
                    <div class="spinner" id="c-spinner"></div>
                </button>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-header">
                <h3>DentCare Pejë</h3>
                <p>Klinikë dentare familjare</p>
            </div>
            <div class="summary-body">
                <div class="clinic-info" style="margin-top:0">
                    <div class="clinic-info-row">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Rr. Mbretëresha Teuta, Pejë
                    </div>
                    <div class="clinic-info-row">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.38 2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.6a16 16 0 0 0 6.29 6.29l.95-.94a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        +383 44 000 000
                    </div>
                    <div class="clinic-info-row">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0a2.25 2.25 0 00-2.25-2.25h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                        info@dentcarepeje.com
                    </div>
                    <div class="clinic-info-row">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Hënë–Sht: 08:00 – 15:00
                    </div>
                </div>

                <div class="social-row">
                    <a class="social-btn" href="#" aria-label="Facebook">
                        <svg viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                    </a>
                    <a class="social-btn" href="#" aria-label="Instagram">
                        <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                    </a>
                    <a class="social-btn" href="https://wa.me/38344000000" aria-label="WhatsApp">
                        <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="map-section" id="lokacioni">
    <h2>Lokacion</h2>
    <p class="section-sub">Na vizitoni në adresën e dhënë.</p>
    <div class="map-container">
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4380.22213610432!2d20.854724782013555!3d42.884496328987794!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x135345e2e1c72257%3A0x12d1a9a4a221ea8c!2sAqua%20Park%20Mitrovica!5e0!3m2!1sen!2s!4v1780244628377!5m2!1sen!2s"
            width="100%"
            height="450"
            style="border:0;"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>
</section>

<footer>
    <p>© 2026 DentCare Pejë. Të gjitha të drejtat e rezervuara.</p>
</footer>

<div class="success-overlay" id="c-success-overlay">
    <div class="success-box">
        <div class="success-icon">
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <h2>Mesazhi u dërgua!</h2>
        <p>Faleminderit që na kontaktuat. Ekipi ynë do t'ju përgjigjet sa më shpejt të jetë e mundur.</p>
        <button class="btn-close" onclick="closeContactSuccess()">Mbylle</button>
    </div>
</div>

<div class="success-overlay error-popup" id="c-error-overlay">
    <div class="success-box" style="border-top: 5px solid #df473c;">
        <div class="success-icon" style="background: #fdf2f2; color: #df473c;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </div>
        <h2 style="color: #1a1a18;">Diçka shkoi keq</h2>
        <p id="c-error-popup-message" style="margin-bottom: 20px; color: #4a4a45; font-size: 15px; line-height: 1.5;"></p>
        <button class="btn-close" onclick="closeContactErrorPopup()" style="background: #df473c;">Provoni Përsëri</button>
    </div>
</div>

<script>
function showContactError(id, show) {
    const el = document.getElementById(id);
    el.style.display = show ? 'block' : 'none';
    const input = document.getElementById(id.replace('c-err-', 'c-'));
    if (input) input.classList.toggle('error', show);
}

async function submitContact() {
    let valid = true;

    const name    = document.getElementById('c-name').value.trim();
    const phone   = document.getElementById('c-phone').value.trim();
    const email   = document.getElementById('c-email').value.trim();
    const subject = document.getElementById('c-subject').value;
    const message = document.getElementById('c-message').value.trim();

    showContactError('c-err-name', !name); if (!name) valid = false;
    showContactError('c-err-email', !email || !email.includes('@')); if (!email || !email.includes('@')) valid = false;
    showContactError('c-err-subject', !subject); if (!subject) valid = false;
    showContactError('c-err-message', !message); if (!message) valid = false;

    if (!valid) return;

    const btn = document.getElementById('c-submit-btn');
    const spinner = document.getElementById('c-spinner');
    const btnText = document.getElementById('c-btn-text');
    btn.disabled = true;
    spinner.style.display = 'block';
    btnText.textContent = 'Duke dërguar...';

    try {
        // Point this to your real backend endpoint, e.g. contact.php,
        // mirroring how index.html posts to book.php
        const res = await fetch('contact.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, phone, email, subject, message })
        });
        const data = await res.json();

        if (data.success) {
            document.getElementById('c-success-overlay').classList.add('show');
            document.getElementById('c-name').value = '';
            document.getElementById('c-phone').value = '';
            document.getElementById('c-email').value = '';
            document.getElementById('c-subject').value = '';
            document.getElementById('c-message').value = '';
        } else {
            document.getElementById('c-error-popup-message').innerText = data.message || 'Ju lutem provoni përsëri më vonë.';
            document.getElementById('c-error-overlay').classList.add('show');
        }
    } catch (e) {
        document.getElementById('c-error-popup-message').innerText = 'Nuk mund të lidhemi me serverin. Kontrolloni lidhjen tuaj.';
        document.getElementById('c-error-overlay').classList.add('show');
    }

    btn.disabled = false;
    spinner.style.display = 'none';
    btnText.textContent = 'Dërgo Mesazhin';
}

function closeContactSuccess() {
    document.getElementById('c-success-overlay').classList.remove('show');
}
function closeContactErrorPopup() {
    document.getElementById('c-error-overlay').classList.remove('show');
}

document.addEventListener("DOMContentLoaded", function() {
    const menuToggle = document.getElementById('menu-toggle');
    const navLinks = document.getElementById('nav-links');
    if (!menuToggle || !navLinks) return;

    menuToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        navLinks.classList.toggle('active');
        menuToggle.classList.toggle('active');
    });

    document.addEventListener('click', function(e) {
        if (!navLinks.contains(e.target) && !menuToggle.contains(e.target)) {
            navLinks.classList.remove('active');
            menuToggle.classList.remove('active');
        }
    });

    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            navLinks.classList.remove('active');
            menuToggle.classList.remove('active');
        });
    });
});
</script>
</body>
</html>