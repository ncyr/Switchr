<script>
    var key = Math.random().toString(36);
</script>
<div id="content-body">
    <section class="title">
        <h2>Add License to Store</h2>
    </section>
    <section class="item">
        <?php if( $message != '' ){ echo '<div class="no_data" style="width: auto;">'.$message.'</div>'; }?>
        <?=form_open('admin/license/add_license')?>
        <fieldset id="store_info">
            <select name="store_id">
                <?php if($storesByOwner): ?>
                <?php foreach($storesByOwner as $store):?>
                <option value="<?=$store->id?>"><?=$store->store_name?></option>
                <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <input type="text" name="key" placeholder="19 Digit Serial Key">
            <input type="hidden" name="granted_by" value="<?=$this->current_user->id?>" />
            <input type="hidden" name="status_code" value="1" />
            <input type="hidden" name="created_at" value="<?=date('Y-m-d h:i:s')?>" />
            <input type="hidden" name="updated_at" value="<?=date('Y-m-d h:i:s')?>" />
            <input type="submit" id="btnSubmit" class="btn green" value="Add License" />
            <input type="submit" class="btn red"  value="Go Back" onclick="history.go(-2);return false;" />
        </fieldset>
        </form>
    </section>
</div>
