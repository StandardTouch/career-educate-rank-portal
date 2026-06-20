<style>
    /* Anti-Copy and Anti-Screenshot Measures */
    body {
        -webkit-user-select: none; /* Safari */
        -moz-user-select: none; /* Firefox */
        -ms-user-select: none; /* IE10+/Edge */
        user-select: none; /* Standard */
        -webkit-touch-callout: none; /* iOS Safari */
    }

    /* Blur content when the window loses focus (deters screen recording/sniping tools) */
    body.blurred-content {
        filter: blur(10px);
        opacity: 0.5;
        pointer-events: none;
    }

    /* Prevent printing */
    @media print {
        html, body {
            display: none !important;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Disable Right Click (Context Menu)
        document.addEventListener('contextmenu', event => event.preventDefault());

        // 2. Disable Keyboard Shortcuts (Copy, Paste, Print, Save, DevTools)
        document.addEventListener('keydown', function(e) {
            // Block PrintScreen keydown
            if (e.key === 'PrintScreen') {
                try {
                    navigator.clipboard.writeText('');
                } catch (err) {}
                e.preventDefault();
                return false;
            }

            // Check for Ctrl (Windows/Linux) or Cmd (Mac)
            const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
            const cmdOrCtrl = isMac ? e.metaKey : e.ctrlKey;

            if (cmdOrCtrl) {
                const key = e.key.toLowerCase();
                // c: Copy, v: Paste, x: Cut, s: Save, p: Print, u: View Source
                if (['c', 'v', 'x', 's', 'p', 'u'].includes(key)) {
                    e.preventDefault();
                    return false;
                }
            }

            // F12 (DevTools)
            if (e.key === 'F12') {
                e.preventDefault();
                return false;
            }

            // Ctrl+Shift+I / Ctrl+Shift+J / Ctrl+Shift+C (DevTools)
            if (e.ctrlKey && e.shiftKey && ['I', 'i', 'J', 'j', 'C', 'c'].includes(e.key)) {
                e.preventDefault();
                return false;
            }
        });

        // 2.5 Disable PrintScreen (keyup) - Windows often fires PrintScreen only on keyup
        document.addEventListener('keyup', function(e) {
            if (e.key === 'PrintScreen') {
                try {
                    navigator.clipboard.writeText('');
                } catch (err) {}
                
                // Add a brief blur effect to ruin the screenshot if it triggers during the flash
                document.body.classList.add('blurred-content');
                setTimeout(() => {
                    document.body.classList.remove('blurred-content');
                }, 1000);
                
                e.preventDefault();
                return false;
            }
        });

        // 3. Clear Clipboard on Copy Attempt
        document.addEventListener('copy', function(e) {
            e.clipboardData.setData('text/plain', 'Content copying is disabled.');
            e.preventDefault();
        });

        document.addEventListener('cut', function(e) {
            e.preventDefault();
        });

        // 4. Blur Page on Focus Loss (Helps deter screen recording tools)
        window.addEventListener('blur', function() {
            document.body.classList.add('blurred-content');
        });

        window.addEventListener('focus', function() {
            document.body.classList.remove('blurred-content');
        });

        // 5. Detect DevTools Open (basic deterrent)
        let devtools = function() {};
        devtools.toString = function() {
            if (!this.opened) {
                // If console is opened, this toString method might get called in some browsers
                // We can take action here, e.g., redirect or show warning
            }
        }
        console.log('%c', devtools);
    });
</script>
