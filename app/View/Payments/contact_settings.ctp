<?php
/**
 * @var array $setting
 */
?>
<?= $this->App->viewStartComment() ?>
<section class="panel company-info paymentMethod has-subnav">
    <?= $this->element('Payment/method_select') ?>
    <h3><?= __("Company Information") ?></h3>
    <form class="form-horizontal" name="editPaySettingsForm" id="editPaySettingsForm" accept-charset="utf-8">
        <input type="hidden" id="editPaySettingsType" value="<?= $setting['type'] ?>"/>
        <div class="form-group">
            <label for="company_name" class="circle-create-label"><?= __("Company Name") ?></label>
            <input type="text" id="company_name" name="company_name" value="<?= $setting['company_name'] ?>"
                    required class="form-control"
                    placeholder="<?= __("ISAO Corporation") ?>" maxlength="255">
        </div>
        <fieldset class="company-info-fieldset">
            <legend class="company-info-legend"><?= __("Company Address") ?></legend>
            <div class="form-group">
                <label for="PaymentsCompanyPostCode" class="circle-create-label"><?= __("Country") ?></label>
                <?php echo $this->Form->input('current_team',
                    array(
                        'type'      => 'select',
                        'options'   => $countries,
                        'value'     => $setting['company_country'],
                        'id'        => 'company_country',
                        'name'      => 'company_country',
                        'label'     => false,
                        'div'       => false,
                        'class'     => 'form-control',
                        'wrapInput' => false,
                        'disabled'  => 'disabled',
                        'form'      => 'editPaySettingsForm',
                    ))
                ?>
            </div>
            <div class="form-group">
                <label for="company_post_code" class="circle-create-label"><?= __("Post Code") ?></label>
                <input type="tel" id="company_post_code" name="company_post_code"
                       value="<?= $setting['company_post_code'] ?>" required
                       class="form-control" placeholder="<?= __("12345") ?>" maxlength="16">
            </div>
            <div class="form-group">
                <label for="company_region"
                       class="circle-create-label"><?= __("State/Province/Region") ?></label>
                <input type="text"
                       id="company_region"
                       name="company_region" value="<?= $setting['company_region'] ?>" required
                       class="form-control"
                       placeholder="<?= __("California") ?>"
                       maxlength="255">
            </div>
            <div class="form-group">
                <label for="company_city"
                       class="circle-create-label"><?= __("City") ?></label>
                <input type="text"
                       id="company_city"
                       name="company_city" value="<?= $setting['company_city'] ?>" required
                       class="form-control"
                       placeholder="<?= __("Los Angeles") ?>"
                       maxlength="255">
            </div>
            <div class="form-group">
                <label for="company_street"
                       class="circle-create-label"><?= __("Street") ?></label>
                <input type="text"
                       id="company_street"
                       name="company_street"
                       value="<?= $setting['company_street'] ?>" required class="form-control"
                       placeholder="<?= __("1234 Street Name") ?>"
                       maxlength="255">
            </div>
        </fieldset>
        <fieldset class="company-info-fieldset">
            <legend class="company-info-legend"><?= __("Company Contact") ?></legend>
            <div class="form-group">
                <div class="flex">
                    <div class="flex-extend mr_8px">
                        <label class="circle-create-label" for="contact_person_last_name"><?= __("Last Name ") ?></label>
                        <input type="text" id="contact_person_last_name"
                               name="contact_person_last_name"
                               value="<?= $setting['contact_person_last_name'] ?>"
                               required
                               class="form-control   mr_8px"
                               maxlength="128"
                               placeholder="<?= __("Smith") ?>">
                    </div>
                    <div class="flex-extend">
                        <label class="circle-create-label" for="contact_person_first_name"><?= __("First Name ") ?></label>
                        <input type="text"
                               id="contact_person_first_name"
                               name="contact_person_first_name"
                               value="<?= $setting['contact_person_first_name'] ?>"
                               required
                               class="form-control"
                               maxlength="128"
                               placeholder="<?= __("John") ?>">
                    </div>
                </div>
            </div>
            <?php if ((int)$setting['type'] === Goalous\Model\Enum\PaymentSetting\Type::INVOICE || true): ?>
            <div class="form-group">
                <div class="flex">
                    <div class="flex-extend mr_8px">
                        <label class="circle-create-label" for="contact_person_last_name_kana"><?= __("Last Name Kana") ?></label>
                        <input type="text" id="contact_person_last_name_kana"
                               name="contact_person_last_name_kana"
                               value="<?= $setting['contact_person_last_name_kana'] ?>"
                               required
                               class="form-control   mr_8px"
                               maxlength="128"
                               placeholder="<?= __("スズキ") ?>">
                    </div>
                    <div class="flex-extend">
                        <label class="circle-create-label" for="contact_person_first_name_kana"><?= __("First Name Kana") ?></label>
                        <input type="text" id="contact_person_first_name_kana"
                               name="contact_person_first_name_kana"
                               value="<?= $setting['contact_person_first_name_kana'] ?>"
                               required
                               maxlength="128"
                               class="form-control  " placeholder="<?= __("タロウ") ?>">
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </fieldset>
        <div class="form-group">
            <label for="contact_person_email" class="circle-create-label"><?= __("Email") ?></label>
            <input type="email"
                   id="contact_person_email"
                    name="contact_person_email"
                    value="<?= $setting['contact_person_email'] ?>"
                    required
                    class="form-control"
                    placeholder="<?= __("name@company.com") ?>" maxlength="255"></div>
        <div class="form-group">
            <label for="contact_person_tel"
                   class="circle-create-label"><?= __("Telephone") ?></label>
            <input type="tel"
                   id="contact_person_tel"
                   name="contact_person_tel"
                   value="<?= $setting['contact_person_tel'] ?>"
                   required
                   class="form-control"
                   placeholder="000-0000-0000"
                   maxlength="255">
        </div>
        <footer>
            <button class="btn btn-primary" id="editPaySettingsSubmitBtn">
                <?= __("Update") ?>
            </button>
        </footer>
    </form>
</section>
<?= $this->App->ViewEndComment() ?>
