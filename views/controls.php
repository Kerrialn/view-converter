<div class="container" style="margin-bottom: 20px;">
    <div class="row">
        {{ theme:partial name="nb_notices" }}

        <div class="page-header row">
            <h1 class="col-md-2">
                <?php echo lang('network_settings:accounting:title:modules')?>
            </h1>
            <div class="col-md-10" style="padding:20px 20px 10px 5px">
                <div class="row">
                    <div class="col-md-offset-1"  style="position:relative">
                        <img src="/addons/main/themes/toucantechV2/img/icon-news-color-a.png" alt="" style="position:absolute;left:-40px;width:25px;height:35px">
                        <?php echo lang('network_settings:accounting:text:accounting_heading')?>
                    </div>
                </div>
            </div>
            <div class="row breadcrumbs" style="clear: both;">
                <h4 class="col-md-6"><?php echo lang('network_settings:accounting:title:accounting_integration') ?> > <?php echo lang('network_settings:accounting:side_menu:controls')?></h4>
            </div>
        </div>

        <div class="col-lg-2">
            <?php echo $this->load->view('accounting/partials/side_menu')?>
        </div>

        <main class="col-lg-10">
            <div class="section">
                <section class="full-col title title-new">
                    <h4 class="accounting-title">
                        <?php echo lang('network_settings:accounting:side_menu:controls')?>
                    </h4>
                </section>

                <?php if ($isAccountingEnabled) : ?>
                <section class="item">
                    <div class="content full-col" style="min-height: 300px;">
                        <?php echo $this->load->view('accounting/partials/control_fields_default', array(
                            'activeSystemID' => $activeSystemID,
                        ))?>
                    </div>
                <section>
                <?php else : ?>
                <section class="item">
                    <div class="content full-col" style="min-height: 300px; text-align: center; font-size: 14px;">
                        <?php echo lang('network_settings:accounting:msg:accounting_modules_must_be_enabled_')?>
                    </div>
                </section>
                <?php endif; ?>

            </div>
        </main>
    </div>
</div>
