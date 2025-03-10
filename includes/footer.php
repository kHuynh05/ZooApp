<footer class="footer">
    <div class="footer-content">
        <div class="footer-left">
            <img src="/assets/img/zoo-logo.jpg" alt="Zoo Logo" class="footer-logo">
            <p class="address">6200 Hermann Park Drive, Houston, TX 77030</p>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Zootopia. All rights reserved</p>
        </div>
    </div>
</footer>

<style>
    .footer {
        background-color: seagreen;
        color: white;
        padding: 2rem;
        width: 100%;
        position: relative;
        bottom: 0;
    }

    .footer-content {
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
    }

    .footer-left {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .footer-logo {
        max-height: 100px;
        width: auto;
    }

    .address {
        font-size: 16px;
        margin: 0;
    }

    .footer-bottom {
        text-align: center;
        margin-top: 2rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .footer-bottom p {
        margin: 0;
        font-size: 14px;
    }
</style>