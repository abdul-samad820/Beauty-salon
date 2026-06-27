<div class="modal fade" id="{{ $id }}" tabindex="-1" role="dialog" aria-labelledby="{{ $id }}-title" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="true">

    <div class="modal-dialog modal-dialog-centered {{ ($size ?? 'md') === 'lg' ? 'modal-lg' : 'modal-md' }}" role="document">
        {{-- card-lux se dark background, border, aur glow effect aayega --}}
        <div class="modal-content card-lux" style="border: 1px solid var(--border); overflow: hidden;">

            <div class="modal-header d-flex align-items-center justify-content-between" style="border-bottom: 1px solid var(--border); padding: 1.2rem 1.5rem; background: rgba(0,0,0,0.2);">
                <h5 class="modal-title lux-modal-title" id="{{ $id }}-title" style="font-family: var(--ff-display); font-size: 1.25rem; font-weight: 400; color: var(--text);">
                    {{ $title }}
                </h5>

                {{-- btn-icon-action se premium close button aayega --}}
                <button type="button" class="btn-icon-action border-0" data-bs-dismiss="modal" onclick="LuxModal.close('{{ $id }}')" aria-label="Close modal" style="background: transparent;">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>

            <div class="modal-body p-4" style="background: var(--bg-card);">
                {{ $slot }}
            </div>

        </div>
    </div>
</div>
