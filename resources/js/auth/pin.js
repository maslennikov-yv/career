const pinBox = document.getElementById('pin-box');

if (!pinBox) {
    // This entry is loaded only on PIN page; guard keeps it harmless elsewhere.
} else {
    const inputs = Array.from(pinBox.querySelectorAll('.pin-input'));
    const status = document.getElementById('pin-status');
    const endpoint = pinBox.dataset.endpoint;
    const csrf = pinBox.dataset.csrf;

    const collectPin = () => inputs.map((i) => i.value).join('');

    const verifyIfReady = async () => {
        const pin = collectPin();
        if (pin.length !== 4 || /[^0-9]/.test(pin)) {
            return;
        }

        status.textContent = 'Проверяем PIN...';

        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                Accept: 'application/json',
            },
            body: JSON.stringify({ pin }),
        });

        const payload = await response.json();

        if (!response.ok) {
            status.textContent = payload.message ?? 'Ошибка проверки PIN';
            return;
        }

        status.textContent = 'PIN подтвержден. Переход...';
        window.location.assign(payload.next);
    };

    inputs.forEach((input, index) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '').slice(-1);
            if (input.value && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
            verifyIfReady();
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Backspace' && !input.value && index > 0) {
                inputs[index - 1].focus();
            }
        });

        input.addEventListener('paste', (event) => {
            event.preventDefault();
            const pasted = (event.clipboardData?.getData('text') ?? '')
                .replace(/\D/g, '')
                .slice(0, 4);

            if (!pasted) {
                return;
            }

            pasted.split('').forEach((digit, i) => {
                if (inputs[i]) {
                    inputs[i].value = digit;
                }
            });

            const nextIndex = Math.min(pasted.length, inputs.length - 1);
            inputs[nextIndex].focus();
            verifyIfReady();
        });
    });
}
