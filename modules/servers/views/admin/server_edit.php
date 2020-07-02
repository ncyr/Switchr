<div id="content-body">
    <section class="title">
        <h2>Store Information</h2>
    </section>
    <section class="item">
        <?=form_open('admin/hosts/edit_host/'.$this->uri->segment(4))?>
        <fieldset id="store_info">
            <ul>
                <?php if($hostsByOwner): ?>
                    <li>
                        <label for="store_name">Store Name</label>
                        <input type="text" name="store_name" value="<?=$hosts->store_name?>" placeholder="<?=$hosts->store_name?>" />
                    </li>
                    <li>
                        <label for="store_desc">Store Descripton</label>
                        <input type="text" name="store_desc" value="<?=$hosts->store_desc?>" placeholder="<?=$hosts->store_desc?>"/>
                    </li>
                    <li>
                        <label for="ssh_host">SSH Hostname</label>
                        <input type="text" name="ssh_host" value="<?=$hosts->ssh_host?>" placeholder="<?=$hosts->ssh_host?>"/>
                    </li>
                    <li>
                        <label for="ssh_port">SSH Port</label>
                        <input type="text" name="ssh_port" value="<?=$hosts->ssh_port?>" placeholder="<?=$hosts->ssh_port?>"/>
                    </li>
                    <li>
                        <label for="ssh_user">SSH Username</label>
                        <input type="text" name="ssh_user" value="<?=$hosts->ssh_user?>" placeholder="<?=$hosts->ssh_user?>"/>
                    </li>
                    <li>
                        <label for="ssh_pass">SSH Password</label>
                        <input type="password" name="ssh_pass" placeholder="*******************" value="<?=$this->encrypt->decode($hosts->ssh_pass)?>"/>
                    </li>
                    <li>
                        <label for="active">Status</label>
                        <input type="checkbox" name="status" value=1 <?php if($hosts->status == 1){ echo 'checked=checked'; }?> />
                    </li>
                    <li>
                        <input type="submit" id="btnSubmit" value="Save Changes" />
                        <input type="submit" value="Go Back" onclick="history.go(-1);return false;" />
                    </li>
                <?php endif ?>
            </ul>
        </fieldset>
        </form>
    </section>
</div>