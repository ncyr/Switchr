<div id="body" class="section">
    {{ if user:logged_in }}
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2>{{ template:title }}<a id="addHost" class="btn btn-default" href="/hosts/network/{{ url:segments segment="3" }}">
                <span class="glyphicon glyphicon-retweet"></span> Refresh View
            </a></h2>
        </div>
        <table class="table table-striped datatable">
            <thead>
                <tr>
                    <th>
                        Host Name
                    </th>
                    <th>
                        Description
                    </th>
                </tr>
            </thead>
            <tbody>
                {{ if not error }}
                <?php foreach($hosts as $host):?>
                <tr>
                    <td>
                        <?=$host[0];?>
                    </td>
                    <td>
                        <?=$host[1];?> <?=$host[2];?> <?=$host[3];?> <?=$host[4];?>
                    </td>
                </tr>
                <?php endforeach; ?>
                {{ else }}
                    <div class="alert alert-danger" role="alert">
                        <strong>Oh snap!</strong> We are unable to make the connection to host at this time, it may be down. Please check the connection, and try again. Contact support for further help.
                    </div>
                {{ endif }}
            </tbody>
            <tfoot>

            </tfoot>
        </table>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2>Arp Table</h2>
        </div>
        <table class="table">

            <tbody>
                {{ if not error }}
                <?php foreach($arp as $row):?>
                            <?php $split = array_filter( preg_split('/[\s]+/', $row) );?>
                            <?php foreach($split as $col):?>
                                <?php $col = trim($col);?>
                                <?php if ($col != "Address" && $col != 'Type' && $col != 'Physical' && $col != 'Internet' && $col != '---' && $col !='ssh:'):?>
                                    <?php if($col == 'Interface:'):?>
                                    <td class="arp-label">
                                        <span style="font-weight:bold"><?=$col?></span>
                                    </td>
                                    <?php else: ?>
                                        <td>
                                            <?=$col?>
                                        </td>
                                    <?php endif; ?>
                                </td>
                                <?php endif;?>
                            <?php endforeach;?>
                    </tr>
                <?php endforeach;?>
                {{ endif }}
            </tbody>
            <tfoot>

            </tfoot>
        </table>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2>System Users</h2>
        </div>
        <ul class="list-group" >
            <?php foreach($users as $user):?>

                <?php $user = array_filter( preg_split('/[\s]+/', $user) ); ?>
                <?php foreach($user as $row):?>
                    <li class="list-group-item">
                    <?=$row;?> <a href=""><span style="float:right" class="label label-primary btn-sm changepw">Change Password</span></a>
                    </li>
                <?php endforeach;?>
            <?php endforeach;?>
        </ul>
    </div>
</div>
{{ else }}
<p><a href="users/login">Please login first.</a></p>
{{ endif }}
<div id="changePasswordForm" class="dialog" style="display: none;" title="Change Password">
    <div class="section">
        {{ if user:logged_in }}
                <form method="post" action="/hosts/change_password/{{ url:segments segment="3"}}/<?=$row?>">
                    <label for="password">Enter New Password</label>
                    <input type="password" name="password" />
                    <label for="password">Confirm New Password</label>
                    <input type="password" name="password-confirm" />
                    <input type="submit" value="Change" name="submitBtn"/>
                </form>
                <div class="input-error" style="display: none;">
                    <span style="float:left" class="glyphicon glyphicon-alert"></span>
                    <p></p>
                </div>
        {{ else }}
            <p><a href="users/login">Please login first.</a></p>
        {{ endif }}
    </div>
</div>
