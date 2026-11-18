@extends('layouts.auth')

@section('auth-title', old('recovery_code') || $errors->has('recovery_code') ? 'Recovery code' : 'Authentication code')
@section('auth-description', old('recovery_code') || $errors->has('recovery_code') ? 'Please confirm access to your account by entering one of your emergency recovery codes.' : 'Enter the authentication code provided by your authenticator application.')

@section('content')
    @php
        $showRecovery = old('recovery_code') || $errors->has('recovery_code');
        $oldCode = old('code', '');
    @endphp

    <form class="auth-card__form" method="POST" action="{{ route('two-factor.login') }}" data-two-factor-form>
        @csrf
        <input type="hidden" name="code" id="code" value="{{ $oldCode }}" data-two-factor-code />

        <div data-otp-panel class="{{ $showRecovery ? 'hidden' : '' }}">
            <div class="form-field">
                <div class="otp-inputs" data-otp-group>
                    @for ($i = 0; $i < 6; $i++)
                        <input
                            class="input"
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="1"
                            autocomplete="one-time-code"
                            aria-label="Authentication code digit {{ $i + 1 }}"
                            value="{{ $oldCode[$i] ?? '' }}"
                            data-otp-digit
                        />
                    @endfor
                </div>
                @error('code')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="form-field {{ $showRecovery ? '' : 'hidden' }}" data-recovery-panel>
            <input
                class="input"
                id="recovery_code"
                name="recovery_code"
                type="text"
                placeholder="Enter recovery code"
                aria-label="Enter recovery code"
                value="{{ old('recovery_code') }}"
                required
                autofocus
                data-recovery-input
                @error('recovery_code') aria-invalid="true" @enderror
            />
            @error('recovery_code')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <button class="btn btn--block btn--lg" type="submit">Continue</button>

        <div class="auth-card__footer">
            <span>or you can </span>
            <button class="btn btn--link btn--sm" type="button" data-two-factor-toggle>
                {{ $showRecovery ? 'login using an authentication code' : 'login using a recovery code' }}
            </button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            var form = document.querySelector('[data-two-factor-form]');

            if (!form) {
                return;
            }

            var otpPanel = form.querySelector('[data-otp-panel]');
            var recoveryPanel = form.querySelector('[data-recovery-panel]');
            var toggle = form.querySelector('[data-two-factor-toggle]');
            var hiddenCode = form.querySelector('[data-two-factor-code]');
            var recoveryInput = form.querySelector('[data-recovery-input]');
            var digits = Array.prototype.slice.call(form.querySelectorAll('[data-otp-digit]'));
            var title = document.querySelector('.auth-card__title');
            var description = document.querySelector('.auth-card__description');
            var showingRecovery = !otpPanel.classList.contains('hidden');

            function syncCode() {
                if (hiddenCode) {
                    hiddenCode.value = digits.map(function (digit) {
                        return digit.value.replace(/\D/g, '').slice(-1);
                    }).join('');
                }
            }

            function setMode(recovery) {
                showingRecovery = recovery;
                otpPanel.classList.toggle('hidden', recovery);
                recoveryPanel.classList.toggle('hidden', !recovery);

                digits.forEach(function (digit) {
                    digit.disabled = recovery;
                });

                if (hiddenCode) {
                    hiddenCode.disabled = recovery;
                }

                if (recoveryInput) {
                    recoveryInput.disabled = !recovery;
                }

                if (toggle) {
                    toggle.textContent = recovery
                        ? 'login using an authentication code'
                        : 'login using a recovery code';
                }

                if (title) {
                    title.textContent = recovery ? 'Recovery code' : 'Authentication code';
                }

                if (description) {
                    description.textContent = recovery
                        ? 'Please confirm access to your account by entering one of your emergency recovery codes.'
                        : 'Enter the authentication code provided by your authenticator application.';
                }
            }

            digits.forEach(function (digit, index) {
                digit.addEventListener('input', function () {
                    digit.value = digit.value.replace(/\D/g, '').slice(-1);
                    syncCode();

                    if (digit.value && index < digits.length - 1) {
                        digits[index + 1].focus();
                    }
                });

                digit.addEventListener('keydown', function (event) {
                    if (event.key === 'Backspace' && !digit.value && index > 0) {
                        digits[index - 1].value = '';
                        syncCode();
                        digits[index - 1].focus();
                    }
                });

                digit.addEventListener('paste', function (event) {
                    var text = (event.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');

                    if (!text) {
                        return;
                    }

                    event.preventDefault();

                    for (var i = 0; i < digits.length - index && i < text.length; i++) {
                        digits[index + i].value = text.charAt(i);
                    }

                    syncCode();
                    digits[Math.min(index + text.length, digits.length - 1)].focus();
                });
            });

            if (toggle) {
                toggle.addEventListener('click', function () {
                    setMode(!showingRecovery);

                    if (showingRecovery && recoveryInput) {
                        recoveryInput.focus();
                    } else if (digits[0]) {
                        digits[0].focus();
                    }
                });
            }

            form.addEventListener('submit', function () {
                syncCode();
            });

            setMode(showingRecovery);

            if (showingRecovery && recoveryInput) {
                recoveryInput.focus();
            } else if (digits[0]) {
                digits[0].focus();
            }
        })();
    </script>
@endpush
