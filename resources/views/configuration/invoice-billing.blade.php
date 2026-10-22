@php
    $isExtendedLicence = (getAppSettings('product_registration', 'licence') === 'dee257a8c3a2656b7d7fbe9a91dd8c7c41d90dc9');
@endphp
<!-- Page Heading -->
<h1><?= __tr('Invoice & Billing Settings') ?></h1>
<!-- /Page Heading -->
<hr>

@if($isExtendedLicence)
    <div class="row">
    <div class="col-xl-6">
    <!-- Invoice & Billing Settings Form -->
    <form id="lwInvoiceBillingForm" class="lw-ajax-form lw-form" method="post" action="<?= route('manage.configuration.write', ['pageType' => request()->pageType]) ?>">
        <div class="row">
            <div class="col-lg-12">
                <!-- Allow Vendor to Download Invoice -->
                <div class="form-group">
                    <x-lw.checkbox :offValue="0" id="lwAllowVendorToDownloadInvoice" name="allow_vendor_to_download_invoice" data-lw-plugin="lwSwitchery" :checked="getAppSettings('allow_vendor_to_download_invoice')" :label="__tr('Allow Vendor to Download Invoice')" value="1" />
                </div>

                <!-- Tax Label -->
                <div class="form-group">
                    <label for="lwTaxLabel"><?= __tr('Tax Label') ?></label>
                    <input type="text" class="form-control form-control-user" name="tax_label" id="lwTaxLabel" value="<?= getAppSettings('tax_label') ?>" placeholder="<?= __tr('GST, VAT, Sales Tax, etc.') ?>">
                </div>

                <!-- Tax Rate -->
                <div class="form-group">
                    <label for="lwTaxRate"><?= __tr('Tax Rate') ?></label>
                    <input type="number" step="any" min="0" class="form-control form-control-user" name="tax_rate" id="lwTaxRate" value="<?= getAppSettings('tax_rate') ?>" placeholder="<?= __tr('e.g. 5, 12, 18, 28') ?>">
                </div>

                <!-- Additional Info -->
                <div class="form-group">
                    <label for="lwInvoiceAdditionalInfo"><?= __tr('Additional Info') ?></label>
                    <textarea name="invoice_additional_info" id="lwInvoiceAdditionalInfo" class="form-control" rows="4" placeholder="<?= __tr('Company registration, payment instructions, terms, legal disclaimers, etc.') ?>"><?= getAppSettings('invoice_additional_info') ?></textarea>
                </div>

                <!-- Invoice Logo -->
                <div class="form-group mt-4">
                    <label for="lwUploadInvoiceLogo"><?= __tr('Invoice Logo') ?></label>
                    <div class="alert alert-default">
                        {{  __tr('Upload will be processed automatically on valid selection.') }}
                    </div>
                    <div class="row">
                        <div class="col-lg-4">
                            <input type="file" data-lw-plugin="lwUploader" data-label-idle="{{ __tr('Select New Invoice Logo') }}" data-allow-revert="true" data-instant-upload="true" data-action="<?= route('media.upload_invoice_logo') ?>" id="lwUploadInvoiceLogo" data-callback="afterUploadedInvoiceLogo" data-default-image-url="<?= getAppSettings('invoice_logo_image_url') ?>">
                        </div>
                    </div>
                </div>

                <!-- Brand Name -->
                <div class="form-group mt-3">
                    <label for="lwInvoiceBrandName"><?= __tr('Brand Name') ?></label>
                    <input type="text" class="form-control form-control-user" name="invoice_brand_name" id="lwInvoiceBrandName" value="<?= getAppSettings('invoice_brand_name') ?>" placeholder="<?= __tr('e.g. Acme Corp') ?>">
                </div>

                <!-- Address -->
                <div class="form-group">
                    <label for="lwInvoiceAddress"><?= __tr('Address') ?></label>
                    <textarea name="invoice_address" id="lwInvoiceAddress" class="form-control" rows="3" placeholder="<?= __tr('Full billing address') ?>"><?= getAppSettings('invoice_address') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Update Button -->
        <div class="mt-4">
            <a href class="lw-ajax-form-submit-action btn btn-primary btn-user lw-btn-block-mobile">
                <?= __tr('Save') ?>
            </a>
        </div>
    </form>
    <!-- /Invoice & Billing Settings Form -->
    </div>
    <!-- Invoice Preview -->
    <div class="col-xl-6 mt-4 mt-xl-0">
        <fieldset class="h-100">
            <legend>
                <?= __tr('Invoice Preview') ?>
                <small class="text-muted"><?= __tr('Sample data, updates as you type') ?></small>
            </legend>
            <div class="mb-2">
                <button type="button" class="btn btn-light btn-sm" onclick="lwRefreshInvoicePreview()"><i class="fa fa-sync"></i> <?= __tr('Refresh Preview') ?></button>
                <button type="button" class="btn btn-light btn-sm" onclick="lwOpenInvoicePreview()"><i class="fas fa-external-link-alt"></i> <?= __tr('Open in New Tab') ?></button>
            </div>
            <iframe id="lwInvoicePreviewFrame" title="<?= __tr('Invoice Preview') ?>" src="<?= route('manage.configuration.invoice_billing_preview') ?>" style="width: 100%; height: 760px; border: 1px solid #e3e6f0; border-radius: 6px; background: #fff;"></iframe>
            <small class="form-text text-muted"><?= __tr('Unsaved changes are shown here, click Save to apply them to real invoices.') ?></small>
        </fieldset>
    </div>
    <!-- /Invoice Preview -->
    </div>
@else
    <div class="alert alert-warning my-3">
        <strong title="Extended Licence Required"><?= __tr('Extended Licence Required') ?></strong> <br>
        <?= __tr('Extended licence is required to access Billing & Invoice Settings.') ?>
    </div>
@endif

@push('appScripts')
<script>
    (function($) {
        'use strict';
        var previewBaseUrl = "<?= route('manage.configuration.invoice_billing_preview') ?>",
            previewFields = '[name="invoice_brand_name"], [name="invoice_address"], [name="tax_label"], [name="tax_rate"], [name="invoice_additional_info"]',
            previewTimer = null;

        var buildInvoicePreviewUrl = function () {
            return previewBaseUrl + '?' + $('#lwInvoiceBillingForm').find(previewFields).serialize() + '&_t=' + Date.now();
        };

        window.lwRefreshInvoicePreview = function () {
            $('#lwInvoicePreviewFrame').attr('src', buildInvoicePreviewUrl());
        };

        window.lwOpenInvoicePreview = function () {
            window.open(buildInvoicePreviewUrl(), '_blank');
        };

        $('#lwInvoiceBillingForm').on('input change', previewFields, function () {
            clearTimeout(previewTimer);
            previewTimer = setTimeout(window.lwRefreshInvoicePreview, 600);
        });

        // After invoice logo file successfully uploaded then this function is called
        window.afterUploadedInvoiceLogo = function (responseData) {
            setTimeout(window.lwRefreshInvoicePreview, 500);
        }
    })(jQuery);
</script>
@endpush
