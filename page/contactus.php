<?php
date_default_timezone_set('Asia/Manila');
?>

<style>
    :root {
        --tau-green: #28a745;
        --tau-green-soft: #eaf7ee;
    }

    /* PAGE WRAPPER */
    .contact-container {
        max-width: 900px;
        padding: 20px;
    }

    /* CARD */
    .contact-card {
        border: 1px solid rgba(40, 167, 69, 0.25);
        border-radius: 16px;
        background: #fff;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    /* HEADER */
    .contact-title {
        text-align: center;
        color: var(--tau-green);
        font-weight: 700;
        margin-bottom: 30px;
    }

    /* GRID */
    .contact-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        align-items: stretch;
        /* IMPORTANT: equal row height */
    }

    /* ITEM */
    .contact-item {
        background: var(--tau-green-soft);
        border-radius: 14px;
        padding: 25px 15px;
        text-align: center;
        transition: 0.25s ease;
        border: 1px solid rgba(40, 167, 69, 0.15);

        width: 100%;
        max-width: 260px;

        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .contact-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(40, 167, 69, 0.15);
    }

    /* ICON */
    .contact-icon {
        font-size: 48px;
        color: var(--tau-green);
        margin-bottom: 12px;
    }

    /* LABEL */
    .contact-label {
        font-weight: 700;
        margin-bottom: 6px;
        color: #2f3e2f;
    }

    /* TEXT */
    .contact-text {
        color: #4a5a4a;
        font-size: 15px;
        word-wrap: break-word;
    }

    .contact-text a {
        color: #1877f2;
        font-weight: 600;
        text-decoration: none;
    }

    .contact-text a:hover {
        text-decoration: underline;
    }

    /* Center last item if alone */
    .contact-item:last-child:nth-child(3n + 1) {
        grid-column: 2 / 3;
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .contact-grid {
            grid-template-columns: 1fr;
        }

        .contact-item {
            max-width: 100%;
        }
    }
</style>

<div class="contact-container mx-auto">

    <div class="contact-card">

        <h2 class="contact-title">Contact Us</h2>

        <div class="contact-grid">

            <!-- EMAIL -->
            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="contact-label">Email</div>
                <div class="contact-text">
                    tau_hrmo-rsp@tau.edu.ph<br>
                    presoffice@tau.edu.ph
                </div>
            </div>

            <!-- FB PAGE -->
            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fab fa-facebook"></i>
                </div>
                <div class="contact-label">FB Page</div>
                <div class="contact-text">
                    <a href="https://www.facebook.com/tarlacagriculturaluniversity" target="_blank">
                        TAU Facebook Page
                    </a>
                </div>
                <div class="contact-text">
                    <a href="https://www.facebook.com/TAUHRMO" target="_blank">
                        TAU-HRMO Facebook Page
                    </a>
                </div>
            </div>

            <!-- WEBSITE -->
            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-globe"></i>
                </div>
                <div class="contact-label">Website</div>
                <div class="contact-text">
                    <a href="https://www.tau.edu.ph/" target="_blank">
                        TAU Website
                    </a>
                </div>
            </div>

            <!-- LOCATION -->
            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="contact-label">Location</div>
                <div class="contact-text">
                    Tarlac Agricultural University<br>
                    Malacampa, Camiling, Tarlac
                </div>
            </div>

        </div>

    </div>

</div>
