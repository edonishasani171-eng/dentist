<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DentCare Pejë — Book an Appointment</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="api/style.css">
</head>
<body>
<nav>
    <a href="#" class="nav-logo">
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
        <li><a href="#lokacioni">Lokacioni</a></li>
        <li><a href="#booking">Rezervoni</a></li>
        <li><a href="tel:+38344000000" class="nav-phone">+383 44 000 000</a></li>
    </ul>
</nav>

<section class="hero">
    <div class="hero-bg-circle"></div> <div class="hero-bg-circle-bottom"></div> <div class="hero-tag">Tani pranojm aplikimet online</div>
    <h1>Kujdesu për <em>Buzëqeshjen tënde</em></h1>
    <p class="hero-sub">Aplikoni termin tuaj dental në më pak se 2 minuta. Asnjë thirrje telefoni, asnjë presje — thjesht zgjidhni kohën tuaj dhe ne do të bëjmë të gjitha gjera të tjera.</p>
    <div class="hero-stats">
        <div class="hero-stat"><strong>300+</strong><span>Pacientë të trajtuar</span></div>
        <div class="hero-stat"><strong>12 vjet</strong><span>Eksperienca</span></div>
        <div class="hero-stat"><strong>4.9★</strong><span>Shqyrtimi i pacientëve</span></div>
    </div>
</section>

<section class="booking-section" id="booking">
    <h2>Rezervoni Terminin tuaj</h2>
    <p class="section-sub">Plotësoni formularin më poshtë — kjo zgjatë një minutë</p>

    <div class="services-strip" id="services">
        <div class="service-pill active" onclick="selectService(this, 'Check-up & Cleaning')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
            Kontroll & Pastrim
        </div>
        <div class="service-pill" onclick="selectService(this, 'Tooth Filling')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a7 7 0 017 7c0 5-7 13-7 13S5 14 5 9a7 7 0 017-7z"/></svg>
            Buzëmbushje
        </div>
        <div class="service-pill" onclick="selectService(this, 'Tooth Extraction')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            Nxjerrje Dhëmbi
        </div>
        <div class="service-pill" onclick="selectService(this, 'Teeth Whitening')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/></svg>
            Zbarëdhim Dhëmbësh
        </div>
        <div class="service-pill" onclick="selectService(this, 'Root Canal')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            Kanal Rrënjë
        </div>
        <div class="service-pill" onclick="selectService(this, 'Orthodontics Consultation')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            Konsultë Ortodontike
        </div>
        <div class="service-pill" onclick="selectService(this, 'Dental X-Ray')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
            Rëntgen Dental
        </div>
    </div>

    <div class="booking-grid">
        <div class="form-card">
            <div id="alert-box" class="alert alert-error"></div>

            <div class="form-section">
                <div class="form-section-title">
                    <div class="section-num">1</div>
                    <h3>Informacioni Juaj</h3>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label for="name">Emri i Plotë *</label>
                        <input type="text" id="name" placeholder="Besa Krasniqi" oninput="updateSummary()">
                        <span class="field-error" id="err-name">Ju lutemi jepni emrin tuaj të plotë</span>
                    </div>
                    <div class="field">
                        <label for="phone">Numri i Telefonit *</label>
                        <input type="tel" id="phone" placeholder="+383 44 ..." oninput="updateSummary()">
                        <span class="field-error" id="err-phone">Ju lutemi jepni numrin e telefonit</span>
                    </div>
                </div>
                <div class="form-row single" style="margin-top:14px">
                    <div class="field">
                        <label for="email">Email Adresa *</label>
                        <input type="email" id="email" placeholder="besa@email.com" oninput="updateSummary()">
                        <span class="field-error" id="err-email">Ju lutemi jepni një email të vlefshëm</span>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">
                    <div class="section-num">2</div>
                    <h3>Shërbimi & Datë</h3>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label for="service">Lloji i Shërbimit *</label>
                        <select id="service" onchange="updateSummary()">
                            <option value="">Zgjidhni një shërbim...</option>
                            <option>Kontroll & Pastrim</option>
                            <option>Buzëmbushje</option>
                            <option>Nxjerrje Dhëmbi</option>
                            <option>Zbarëdhim Dhëmbësh</option>
                            <option>Kanal Rrënjë</option>
                            <option>Konsultë Ortodontike</option>
                            <option>Rëntgen Dental</option>
                        </select>
                        <span class="field-error" id="err-service">Ju lutemi jepni një shërbim</span>
                    </div>
                    <div class="field">
                        <label for="date">Data e Preferuar *</label>
                        <input type="date" id="date" onchange="loadTimeSlots()" oninput="updateSummary()">
                        <span class="field-error" id="err-date">Ju lutemi jepni një datë</span>
                    </div>
                </div>
                <div class="form-row single" style="margin-top:14px">
                    <div class="field">
                        <label for="notes">Shënime për doktorin</label>
                        <textarea id="notes" placeholder="Cilësi alergjikë, shqyrtime ose informacion që dentisti duhet të njoh..."></textarea>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">
                    <div class="section-num">3</div>
                    <h3>Zgjidhni një kohë të lirë</h3>
                </div>
                <div class="time-grid" id="time-grid">
                    <div class="slots-placeholder">Zgjidhni një datë më lart për të parë kohët e lira</div>
                </div>
                <span class="field-error" id="err-time" style="display:none;margin-top:8px">Please select a time slot</span>
            </div>

            <div class="submit-area">
                <button class="btn-submit" onclick="submitBooking()" id="submit-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span id="btn-text">Konfirmo Terminin</span>
                    <div class="spinner" id="spinner"></div>
                </button>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-header">
                <h3>Aplikimi Juaj</h3>
                <p>Shqyrtime për rezervimin tuaj</p>
            </div>
            <div class="summary-body">
                <div class="summary-row">
                    <div><div class="s-label">Pacienti</div><div class="s-val" id="s-name"><span class="empty">Nuk është plotësuar</span></div></div>
                </div>
                <div class="summary-row">
                    <div><div class="s-label">Shërbimi</div><div class="s-val" id="s-service"><span class="empty">Nuk është zgjedhur</span></div></div>
                </div>
                <div class="summary-row">
                    <div><div class="s-label">Data</div><div class="s-val" id="s-date"><span class="empty">Nuk është zgjedhur</span></div></div>
                </div>
                <div class="summary-row">
                    <div><div class="s-label">Koha</div><div class="s-val" id="s-time"><span class="empty">Nuk është zgjedhur</span></div></div>
                </div>
                <div class="summary-row">
                    <div><div class="s-label">Statusi</div><div class="s-val"><span style="color:#ba7517;font-size:13px">⏳ Në pritje të konfirmimit</span></div></div>
                </div>

                <div class="clinic-info">
                    <div class="clinic-info-row">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Rr. Mbretëresha Teuta, Pejë
                    </div>
                    <div class="clinic-info-row">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.38 2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.6a16 16 0 0 0 6.29 6.29l.95-.94a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        +383 44 000 000
                    </div>
                    <div class="clinic-info-row">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Hënë–Sht: 08:00 – 15:00
                    </div>
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
    <a href="login.php">Portal i Personalit →</a>
</footer>

<div class="success-overlay" id="success-overlay">
    <div class="success-box">
        <div class="success-icon">
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <h2>Aplikimi juaj është i marrë!</h2>
        <p>Termini juaj është i marrë. Do t'i konfirmojmë së shpejti në telefon ose me email.</p>
        <div class="success-detail" id="success-detail"></div>
        <button class="btn-close" onclick="closeSuccess()">Rezervoni një termin tjetër</button>
    </div>
</div>
<div class="success-overlay error-popup" id="error-overlay">
    <div class="success-box" style="border-top: 5px solid #df473c;">
        <div class="success-icon" style="background: #fdf2f2; color: #df473c;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </div>
        <h2 style="color: #1a1a18;">Orari është i zënë!</h2>
        <p id="error-popup-message" style="margin-bottom: 20px; color: #4a4a45; font-size: 15px; line-height: 1.5;"></p>
        <button class="btn-close" onclick="closeErrorPopup()" style="background: #df473c;">Zgjidh një orar tjetër</button>
    </div>
</div>

<script>
const TIMES = ['08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','14:00','14:30','15:00','15:30','16:00','16:30'];
let selectedTime = null;

document.getElementById('date').min = new Date().toISOString().split('T')[0];

function selectService(el, service) {
    document.querySelectorAll('.service-pill').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('service').value = service;
    updateSummary();
}
document.addEventListener("DOMContentLoaded", function () {
    const dateInput = document.querySelector('input[type="date"]');
    
    if (dateInput) {
        const today = new Date();
        const yyyy = today.getFullYear();
        let mm = today.getMonth() + 1; 
        let dd = today.getDate();

        if (mm < 10) mm = '0' + mm;
        if (dd < 10) dd = '0' + dd;

        dateInput.setAttribute('min', `${yyyy}-${mm}-${dd}`);

        dateInput.addEventListener('input', function () {
            if (!this.value) return;

            // Get the day of the week (0 = Sunday)
            const day = new Date(this.value).getUTCDay();
            
            if (day === 0) { 
                // 1. Set the text title and description for your custom error-overlay structure
                const errorTitle = document.querySelector('#error-overlay h2');
                if (errorTitle) errorTitle.textContent = 'Klinika është e mbyllur!';
                
                document.getElementById('error-popup-message').innerText = 
                    'Ditëve të Diela nuk punojmë. Ju lutem zgjidhni një ditë tjetër pune nga e Hëna në të Shtunë.';
                
                // 2. Open your beautiful custom popup layout seamlessly
                document.getElementById('error-overlay').classList.add('show');
                
                // 3. Wipe out the data from the input fields and summary card
                this.value = ''; 
                
                const summaryDate = document.getElementById('s-date');
                if (summaryDate) {
                    summaryDate.innerHTML = '<span class="empty">Nuk është zgjedhur</span>';
                }
                
                // Reset the time grid UI back to standard state
                const grid = document.getElementById('time-grid');
                if (grid) {
                    grid.innerHTML = '<div class="slots-placeholder">Zgjidhni një datë më lart për të parë kohët e lira</div>';
                }
            }
        });
    }
});
async function loadTimeSlots() {
    const date = document.getElementById('date').value;
    if (!date) return;

    // Clear old conflict warnings when picking a fresh date
    document.getElementById('alert-box').style.display = 'none';

    const grid = document.getElementById('time-grid');
    grid.innerHTML = '<div class="slots-placeholder">Loading available slots...</div>';
    selectedTime = null;
    updateSummary();

    try {
        const res = await fetch(`get_slots.php?date=${date}`);
        const data = await res.json();
        renderSlots(data.booked || []);
    } catch (e) {
        renderSlots([]);
    }

    updateSummary();
}

function renderSlots(booked) {
    const grid = document.getElementById('time-grid');
    grid.innerHTML = '';
    
    // 1. Check if the currently selected date is a Saturday
    const dateValue = document.getElementById('date').value;
    let isSaturday = false;
    
    if (dateValue) {
        // Use local date decomposition to prevent timezone shifting
        const parts = dateValue.split('-');
        const selectedDate = new Date(parts[0], parts[1] - 1, parts[2]);
        if (selectedDate.getDay() === 6) { // 6 = Saturday
            isSaturday = true;
        }
    }

    TIMES.forEach(t => {
        // 2. If it's a Saturday and the slot is 15:00 or later, skip rendering it
        if (isSaturday) {
            const [hour, minutes] = t.split(':').map(Number);
            if (hour >= 15) {
                return; // Drops 15:00, 15:30, 16:00, and 16:30 dynamically
            }
        }

        const btn = document.createElement('button');
        btn.className = 'time-btn' + (booked.includes(t) ? ' taken' : '');
        btn.textContent = t;
        btn.disabled = booked.includes(t);
        
        if (!booked.includes(t)) {
            btn.onclick = () => {
                document.querySelectorAll('.time-btn').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                selectedTime = t;
                
                document.getElementById('alert-box').style.display = 'none';
                updateSummary();
            };
        }
        grid.appendChild(btn);
    });

    // Handle edge case: if no slots are available at all
    if (grid.children.length === 0) {
        grid.innerHTML = '<div class="slots-placeholder">Nuk ka orare të lira për këtë ditë.</div>';
    }
}

function updateSummary() {
    const name = document.getElementById('name').value.trim();
    const service = document.getElementById('service').value;
    const date = document.getElementById('date').value;

    document.getElementById('s-name').innerHTML = name
        ? `<strong>${name}</strong>`
        : '<span class="empty">Nuk është plotësuar</span>';

    document.getElementById('s-service').innerHTML = service || '<span class="empty">Nuk është zgjedhur</span>';

    if (date) {
        const d = new Date(date + 'T00:00:00');
        document.getElementById('s-date').innerHTML = d.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    } else {
        document.getElementById('s-date').innerHTML = '<span class="empty">Nuk është zgjedhur</span>';
    }

    document.getElementById('s-time').innerHTML = selectedTime || '<span class="empty">Nuk është zgjedhur</span>';
}

function showError(id, show) {
    const el = document.getElementById(id);
    el.style.display = show ? 'block' : 'none';
    const input = document.getElementById(id.replace('err-', ''));
    if (input) input.classList.toggle('error', show);
}

async function submitBooking() {
    let valid = true;

    const name = document.getElementById('name').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const email = document.getElementById('email').value.trim();
    const service = document.getElementById('service').value;
    const date = document.getElementById('date').value;
    const notes = document.getElementById('notes').value.trim();

    showError('err-name', !name); if (!name) valid = false;
    showError('err-phone', !phone); if (!phone) valid = false;
    showError('err-email', !email || !email.includes('@')); if (!email || !email.includes('@')) valid = false;
    showError('err-service', !service); if (!service) valid = false;
    showError('err-date', !date); if (!date) valid = false;

    // --- NEW PAST DATE VALIDATION ---
    if (date) {
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Clear current hour metrics to check pure calendar dates

        const parts = date.split('-');
        const selectedDate = new Date(parts[0], parts[1] - 1, parts[2]);
        selectedDate.setHours(0, 0, 0, 0);

        if (selectedDate < today) {
            // Revert error popups headers back to warning parameters explicitly
            document.querySelector('#error-overlay h2').textContent = 'Datë e pavlefshme!';
            document.getElementById('error-popup-message').innerText = 'Data e zgjedhur ka kaluar. Ju lutem shkruani ose zgjidhni një datë aktuale ose të ardhshme për terminin tuaj.';
            document.getElementById('error-overlay').classList.add('show');
            valid = false;
        }
    }

    const errTime = document.getElementById('err-time');
    if (!selectedTime) {
        errTime.style.display = 'block';
        valid = false;
    } else {
        errTime.style.display = 'none';
    }

    if (!valid) return;

    const btn = document.getElementById('submit-btn');
    const spinner = document.getElementById('spinner');
    const btnText = document.getElementById('btn-text');
    btn.disabled = true;
    spinner.style.display = 'block';
    btnText.textContent = 'Rezervimi...';

    try {
        const res = await fetch(`book.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, phone, email, service, date, time: selectedTime, notes })
        });
        const data = await res.json();

        if (data.success) {
            const d = new Date(date + 'T00:00:00');
            const dateStr = d.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            document.getElementById('success-detail').innerHTML =
                `<strong>Name:</strong> ${name}<br>
                 <strong>Service:</strong> ${service}<br>
                 <strong>Date:</strong> ${dateStr}<br>
                 <strong>Time:</strong> ${selectedTime}`;
            document.getElementById('success-overlay').classList.add('show');

            // Resetting values cleanly
            document.getElementById('name').value = '';
            document.getElementById('phone').value = '';
            document.getElementById('email').value = '';
            document.getElementById('service').value = '';
            document.getElementById('date').value = '';
            document.getElementById('notes').value = '';
            document.getElementById('time-grid').innerHTML = '<div class="slots-placeholder">Select a date above to see available time slots</div>';
            selectedTime = null;
            updateSummary();
        } else {
            // POP UP THE ERROR OVERLAY VISUALLY
            document.getElementById('error-popup-message').innerText = data.message || 'Ju lutem zgjedhni një datë ose kohë tjetër sepse kjo është e zënë.';
            document.getElementById('error-overlay').classList.add('show');
        }
    } catch (e) {
        document.getElementById('error-popup-message').innerText = 'Could not connect to server. Please check your connection.';
        document.getElementById('error-overlay').classList.add('show');
    }

    btn.disabled = false;
    spinner.style.display = 'none';
    btnText.textContent = 'Konfirmo Terminin';
}
function closeErrorPopup() {
    document.getElementById('error-overlay').classList.remove('show');
}
document.addEventListener("DOMContentLoaded", function() {
    const menuToggle = document.getElementById('menu-toggle');
    const navLinks = document.getElementById('nav-links');

    // Kontrollojmë në konsolë nëse JS po i gjen elementet
    if (!menuToggle || !navLinks) {
        console.error("Gabim: Nuk u gjet 'menu-toggle' ose 'nav-links' në HTML!");
        return;
    }

    // Funksioni i klikimit
    menuToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        navLinks.classList.toggle('active');
        menuToggle.classList.toggle('active');
    });

    // Mbyll menunë kur klikohet jashtë saj
    document.addEventListener('click', function(e) {
        if (!navLinks.contains(e.target) && !menuToggle.contains(e.target)) {
            navLinks.classList.remove('active');
            menuToggle.classList.remove('active');
        }
    });

    // Mbyll menunë kur klikohet një nga linqet
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            navLinks.classList.remove('active');
            menuToggle.classList.remove('active');
        });
    });
});

function closeSuccess() {
    document.getElementById('success-overlay').classList.remove('show');
    document.querySelectorAll('.service-pill').forEach((p, i) => p.classList.toggle('active', i === 0));
    document.getElementById('service').value = 'Check-up & Cleaning';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
</body>
</html>
