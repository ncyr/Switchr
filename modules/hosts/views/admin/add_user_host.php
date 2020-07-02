<div id="content-body">
    <section class="title">
        <h2>Add User to Host</h2>
    </section>
    <section class="item">
        <div class="alert error" style="width: auto;"><?=$message?></div>
        <?=form_open('admin/hosts/add_host_user')?>
        <select id="hostList" name="host_id">
            <?php foreach($hostsByOwner as $host):?>
            <option value="<?=$host->id?>">
                <?=$host->host_name?>
            </option>
            <?php endforeach;?>
        </select>
        <select id="userList" name="user_id">
            <?php foreach($allUsers->result() as $user):?>
            <option value="<?=$user->id?>">
                <?=$user->email?>
            </option>
            <?php endforeach;?>
        </select>
        <input type="submit" class="btn green" value="Add to Host"/>
        <input type="button" class="btn red" value="Go Back" onclick="history.go(-1);return false;" />
        </form>
    </section>
</div>
