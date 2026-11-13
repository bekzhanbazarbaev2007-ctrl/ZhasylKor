import { Passkeys } from '@laravel/passkeys';
import './greenfund.js';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

const requestJson = async (url, method = 'GET') => {
    const response = await fetch(url, {
        method,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
        },
    });

    if (!response.ok) {
        throw new Error(`Request failed (${response.status}).`);
    }

    return response.json();
};

const reportError = (element, error) => {
    if (element) {
        element.textContent =
            error instanceof Error ? error.message : 'Something went wrong.';
    }
};

document
    .querySelectorAll('[data-passkey-login], [data-passkey-confirm]')
    .forEach((button) => {
        const isConfirm = button.hasAttribute('data-passkey-confirm');
        const errorElement = document.querySelector('[data-passkey-error]');

        if (!Passkeys.isSupported()) {
            button.setAttribute('hidden', '');
            return;
        }

        button.addEventListener('click', async () => {
            button.setAttribute('disabled', '');
            if (errorElement) errorElement.textContent = '';

            try {
                const result = await Passkeys.verify(
                    isConfirm
                        ? {
                              routes: {
                                  options: '/passkeys/confirm/options',
                                  submit: '/passkeys/confirm',
                              },
                          }
                        : {},
                );
                if (result?.redirect) {
                    window.location.assign(result.redirect);
                } else if (isConfirm) {
                    window.location.reload();
                }
            } catch (error) {
                reportError(errorElement, error);
            } finally {
                button.removeAttribute('disabled');
            }
        });
    });

document.querySelectorAll('[data-passkey-register]').forEach((form) => {
    const button = form.querySelector('button[type="submit"]');
    const nameInput = form.querySelector('[name="name"]');
    const errorElement = form.querySelector('[data-passkey-error]');

    if (!Passkeys.isSupported()) {
        if (errorElement)
            errorElement.textContent =
                'Passkeys are not supported by this browser.';
        if (button) button.setAttribute('disabled', '');
        return;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!button || !nameInput) return;

        button.setAttribute('disabled', '');
        if (errorElement) errorElement.textContent = '';
        try {
            await Passkeys.register({ name: nameInput.value.trim() });
            window.location.reload();
        } catch (error) {
            reportError(errorElement, error);
            button.removeAttribute('disabled');
        }
    });
});

document.querySelectorAll('[data-toggle-recovery]').forEach((button) => {
    button.addEventListener('click', () => {
        const code = document.querySelector('#code');
        const recoveryField = document.querySelector('[data-recovery-field]');
        const recoveryCode = document.querySelector('#recovery_code');
        const usingRecovery =
            recoveryField?.classList.contains('hidden') ?? true;

        recoveryField?.classList.toggle('hidden', !usingRecovery);
        if (recoveryCode) recoveryCode.disabled = !usingRecovery;
        if (code) code.disabled = usingRecovery;
        button.textContent = usingRecovery
            ? 'Use an authentication code instead'
            : 'Use a recovery code instead';
        (usingRecovery ? recoveryCode : code)?.focus();
    });
});

document.querySelectorAll('[data-load-two-factor]').forEach((button) => {
    button.addEventListener('click', async () => {
        const container = button.closest('section');
        const qrElement = container?.querySelector('[data-two-factor-qr]');
        const secretElement = container?.querySelector(
            '[data-two-factor-secret]',
        );
        const errorElement = container?.querySelector(
            '[data-two-factor-error]',
        );
        button.setAttribute('disabled', '');

        try {
            const [qr, secret] = await Promise.all([
                requestJson('/user/two-factor-qr-code'),
                requestJson('/user/two-factor-secret-key'),
            ]);
            if (qrElement) qrElement.innerHTML = qr.svg;
            if (secretElement) secretElement.textContent = secret.secretKey;
            if (errorElement) errorElement.textContent = '';
        } catch (error) {
            reportError(errorElement, error);
        } finally {
            button.removeAttribute('disabled');
        }
    });
});

document.querySelectorAll('[data-load-recovery-codes]').forEach((button) => {
    button.addEventListener('click', async () => {
        const container = button.parentElement?.querySelector(
            '[data-recovery-codes]',
        );
        button.setAttribute('disabled', '');
        try {
            const codes = await requestJson('/user/two-factor-recovery-codes');
            if (container) {
                container.textContent = '';
                codes.forEach((code) => {
                    const line = document.createElement('div');
                    line.textContent = code;
                    container.append(line);
                });
                container.classList.remove('hidden');
            }
        } catch (error) {
            reportError(container, error);
            container?.classList.remove('hidden');
        } finally {
            button.removeAttribute('disabled');
        }
    });
});

document.querySelectorAll('[data-appearance]').forEach((button) => {
    button.addEventListener('click', () => {
        const appearance = button.dataset.appearance;
        if (!appearance) return;

        document.cookie = `appearance=${appearance}; Path=/; Max-Age=31536000; SameSite=Lax`;
        document.documentElement.classList.toggle(
            'dark',
            appearance === 'dark' ||
                (appearance === 'system' &&
                    window.matchMedia('(prefers-color-scheme: dark)').matches),
        );
        document.querySelectorAll('[data-appearance]').forEach((option) => {
            option.setAttribute(
                'aria-pressed',
                option === button ? 'true' : 'false',
            );
        });
    });
});

if (
    document.documentElement.classList.contains('dark') === false &&
    document.cookie.split('; ').includes('appearance=system') &&
    window.matchMedia('(prefers-color-scheme: dark)').matches
) {
    document.documentElement.classList.add('dark');
}
