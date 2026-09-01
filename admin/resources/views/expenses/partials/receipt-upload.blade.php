<div>
    <label class="form-label flex items-center gap-1.5">
        <span>📎</span> Upload receipt
    </label>
    <div class="expense-dropzone rounded-xl p-5 text-center cursor-pointer"
         :class="dragOver ? 'dragover' : ''"
         @dragover.prevent="dragOver = true"
         @dragleave.prevent="dragOver = false"
         @drop.prevent="dragOver = false; onReceipt($event); $refs.receipt.files = $event.dataTransfer.files"
         @click="$refs.receipt.click()">
        <input type="file" name="receipt" form="expense-create-form" x-ref="receipt" class="hidden" accept=".jpg,.jpeg,.png,.pdf"
               @change="onReceipt($event)">
        <template x-if="!receiptName">
            <div>
                <p class="text-sm font-medium text-heading">Drag &amp; drop or click to upload</p>
                <p class="text-xs text-muted mt-1">JPG · PNG · PDF · max 5MB</p>
            </div>
        </template>
        <template x-if="receiptName">
            <div>
                <p class="text-sm font-semibold text-velour-600" x-text="receiptName"></p>
                <p class="text-xs text-muted mt-1">Click to replace</p>
            </div>
        </template>
    </div>
</div>
