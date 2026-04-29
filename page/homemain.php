<?php
date_default_timezone_set('Asia/Manila');
?>

<style>
    :root {
        --tau-green: #28a745;
        --tau-green-soft: #eaf7ee;
    }

    .tau-card {
        border: 1px solid rgba(40, 167, 69, 0.25);
        border-radius: 14px;
        margin-bottom: 12px;
        overflow: hidden;
        background: #fff;
        transition: all 0.25s ease;
    }

    .tau-card:hover {
        box-shadow: 0 6px 18px rgba(40, 167, 69, 0.15);
    }

    .tau-header {
        background: linear-gradient(135deg, var(--tau-green), #2ecc71);
        color: #fff;
        padding: 14px 16px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .tau-icon {
        transition: transform 0.3s ease;
    }

    .tau-icon.rotate {
        transform: rotate(180deg);
    }

    .tau-body {
        background: var(--tau-green-soft);

        max-height: 1000px;
        opacity: 1;

        overflow: hidden;
        transition: max-height 0.35s ease, opacity 0.25s ease, padding 0.25s ease;
        padding: 0 16px;
    }

    .tau-body.closed {
        max-height: 0;
        opacity: 0;
        padding-top: 0;
        padding-bottom: 0;
    }

    .tau-body-inner {
        padding: 16px 0;
    }

    .tau-body p,
    .tau-body li {
        margin-bottom: 8px;
        color: #2f3e2f;
    }
</style>

<div class="container pt-4 pb-2">

    <h3 class="mb-4 text-success font-weight-bold">TAU Job Portal</h3>

    <!-- Reminders -->
    <div class="tau-card">
        <div class="tau-header" onclick="toggleCard(this)">
            <span class="font-weight-bold">📢 Reminders & Announcements</span>
            <i class="fas fa-chevron-down tau-icon"></i>
        </div>

        <div class="tau-body">
            <div class="tau-body-inner">
                <ul>
                    <li>
                        TAU highly encourages all interested and qualified applicants to apply, which include persons with disability (PWD) and members of the indigenous communities, irrespective of sexual orientation and gender identity and/or expression, civil status, religion, and political affiliation.
                    </li>

                    <li>
                        TAU does not discriminate in the selection of employees based on the aforementioned pursuant to the Equal Opportunities for Employment Principle (EOP).
                    </li>
                    <li>Ensure that all the following documents are properly reviewed and submitted on or before the deadline:</li>
                    <ul>
                        <li>
                            Application Letter
                        </li>
                        <li>
                            Fully accomplished Personal Data Sheet (PDS) with Work Experience Sheet and recent passport-sized or unfiltered digital picture (CS Form No. 212, Revised 2025); digitally signed or electronically signed;
                        </li>
                        <li>
                            Copy of Performance Rating in the last rating period (if applicable);
                        </li>
                        <li>
                            Copy of proof of eligibility/rating/license;
                        </li>
                        <li>
                            Copy of Transcript of Records;
                        </li>
                        <li>
                            Copy of supporting documents or other credentials (Certificate of Attendance/Participation in Trainings/Seminars, Service Record/Certificate of Employment, Skills Certificate, Certificate of Recognition/Merit)
                        </li>
                    </ul>
                    <li class="font-weight-bold">
                        APPLICATIONS WITH INCOMPLETE DOCUMENTS SHALL NOT BE ENTERTAINED.
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- FAQs -->
    <div class="tau-card">
        <div class="tau-header" onclick="toggleCard(this)">
            <span class="font-weight-bold">❓ Frequently Asked Questions</span>
            <i class="fas fa-chevron-down tau-icon"></i>
        </div>

        <div class="tau-body">
            <div class="tau-body-inner">
                <p><b>How do I apply?</b><br>Go to Manage Profile ➜ Complete your Summary Profile ➜ Go to "Vacancies" ➜ Click "Apply".</p>
                <p><b>Can I apply for multiple positions?</b><br>Yes.</p>
            </div>
        </div>
    </div>

    <!-- About -->
    <div class="tau-card">
        <div class="tau-header" onclick="toggleCard(this)">
            <span class="font-weight-bold">ℹ️ About TAU Job Portal</span>
            <i class="fas fa-chevron-down tau-icon"></i>
        </div>

        <div class="tau-body">
            <div class="tau-body-inner">
                <p>
                    The TAU Job Portal centralizes job postings and applications for faster recruitment.
                </p>
                <p class="font-weight-bold text-danger">
                    — Welcome to the Tarlac Agricultural University Job Portal!
                </p>
            </div>
        </div>
    </div>

</div>

<script>
    function toggleCard(header) {
        const body = header.nextElementSibling;
        const icon = header.querySelector(".tau-icon");

        const isClosed = body.classList.contains("closed");

        if (isClosed) {
            body.classList.remove("closed");
            icon.classList.add("rotate");
        } else {
            body.classList.add("closed");
            icon.classList.remove("rotate");
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".tau-body").forEach(body => {
            body.classList.remove("closed");

            const icon = body.parentElement.querySelector(".tau-icon");
            if (icon) icon.classList.add("rotate");
        });
    });
</script>
