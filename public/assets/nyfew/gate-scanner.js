(function () {
    'use strict';

    const startButton = document.getElementById('gate-start');
    if (!startButton) return;

    const stopButton = document.getElementById('gate-stop');
    const message = document.getElementById('gate-camera-message');
    const form = document.getElementById('gate-lookup-form');
    const input = document.getElementById('gate-reference');
    const eventSelect = document.getElementById('gate-event');
    const idle = document.getElementById('gate-camera-idle');
    const readerElement = document.getElementById('gate-reader');

    let scanner = null;
    let running = false;
    let starting = false;
    let verified = false;

    const notify = (text) => { message.textContent = text; };
    function setButtons(isRunning) {
        startButton.disabled = isRunning || starting;
        stopButton.disabled = !isRunning;
    }

    async function stopScanner() {
        const current = scanner;
        scanner = null;
        running = false;
        starting = false;
        setButtons(false);
        if (current) {
            try {
                if (current.isScanning) await current.stop();
            } catch (err) { /* Camera may already have stopped. */ }
            try { current.clear(); } catch (err) { /* Non-fatal cleanup. */ }
        }
        readerElement.style.display = 'none';
        readerElement.replaceChildren();
        idle.style.display = '';
    }

    startButton.addEventListener('click', async function () {
        if (starting || running) return;
        if (!eventSelect.value) {
            notify('Select the event first.');
            eventSelect.focus();
            return;
        }
        if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
            notify('Camera access requires HTTPS on a phone, or localhost on your computer.');
            return;
        }
        if (typeof window.Html5Qrcode === 'undefined') {
            notify('QR scanner library could not load. Check your internet connection or use manual lookup.');
            return;
        }

        starting = true;
        setButtons(false);
        readerElement.style.display = '';
        idle.style.display = 'none';
        notify('Requesting camera permission...');
        verified = false;
        try {
            scanner = new window.Html5Qrcode('gate-reader', { verbose: false });
            await scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: (w, h) => {
                    const size = Math.min(260, Math.floor(Math.min(w, h) * 0.8));
                    return {width: Math.max(size, 100), height: Math.max(size, 100)};
                } },
                async function (result) {
                    if (verified) return;
                    verified = true;
                    input.value = result;
                    notify('QR code detected. Checking the ticket...');
                    await stopScanner();
                    if (typeof form.requestSubmit === 'function') form.requestSubmit();
                    else form.submit();
                },
                function () { /* No QR in this frame: keep scanning. */ }
            );
            starting = false;
            running = true;
            setButtons(true);
            notify('Camera ready. Hold the ticket QR code inside the frame.');
        } catch (error) {
            await stopScanner();
            notify('Could not open the camera. Allow camera permission, close other apps using it, or try another camera.');
        }
    });

    stopButton.addEventListener('click', async function () {
        await stopScanner();
        notify('Camera stopped.');
    });
    window.addEventListener('pagehide', function () {
        // Release the camera when the operator leaves the gate page.
        if (scanner?.isScanning) scanner.stop().catch(() => {});
    });
})();
