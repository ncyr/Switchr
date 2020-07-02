<div id="content-body">
    <section class="title">
        <h2>Add a Store</h2>
    </section>
    <section class="item">
        <?=form_open('admin/stores/add_store/'.$this->uri->segment(4))?>
        <fieldset id="store_info">
            <ul>
                <?php if($storesByOwner): ?>
                    <li>
                        <label for="store_name">Store Name</label>
                        <input type="text" name="store_name" placeholder="Store Name" />
                    </li>
                    <li>
                        <label for="store_desc">Store Descripton</label>
                        <input type="text" name="store_desc" placeholder="Store Description"/>
                    </li>
                    <li>
                        <label for="ssh_host">SSH Hostname</label>
                        <input type="text" name="ssh_host" placeholder="SSH Host"/>
                    </li>
                    <li>
                        <label for="ssh_port">SSH Port</label>
                        <input type="text" name="ssh_port" placeholder="SSH Port"/>
                    </li>
                    <li>
                        <label for="ssh_user">SSH Username</label>
                        <input type="text" name="ssh_user" placeholder="SSH Username"/>
                    </li>
                    <li>
                        <label for="ssh_pass">SSH Password</label>
                        <input type="password" name="ssh_pass" placeholder="SSH Password"/>
                    </li>
                    <li>
                        <label for="active">Status</label>
                        <input type="checkbox" name="status" value=1 />
                    </li>
                    <li>
                        <input type="submit" id="btnSubmit" value="Add User" />
                        <input type="submit" value="Go Back" onclick="history.go(-1);return false;" />
                    </li>
                <?php endif ?>
            </ul>
        </fieldset>
        </form>
    </section>
</div>
