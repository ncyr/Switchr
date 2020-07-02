
<div id="body" class="section">
    {{ if user:logged_in }}
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2>Your Hosts<a id="addHost" class="btn btn-default btn" href="/hosts/create">
                <span class="glyphicon glyphicon-plus"></span>Create Host
            </a></h2>
        </div>
        <table class="table table-striped datatable">
            <thead>
                <tr>
                    <th>
                        <a class="help" title="Host Name" description="This the descriptive name for the host.">Name</a>
                    </th>
                    <th>
                        <a class="help" title="Last Online" description="The host should report every 30-60 seconds or so. If it does not, you may see the last time it did.">Last Online</a>
                    </th>
                    <th>
                        <a class="help" title="IP Address" description="This will be the address you use to access your ports, and data.">IP Address</a>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hosts as $host) : ?>
                    <tr class="{{ odd_even }}">
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-default confirm-restart-host" title="Restart the host">
                                    <?php if ($host['host_status_timestamp'] >= date('U', strtotime('-40 seconds'))) : ?>
                                        <a href="javascript:" title="Restart the host" class="status-on" id="<?php echo $host['id']; ?>">
                                            <span class="glyphicon glyphicon-off"></span>
                                        </a>
                                    <?php else : ?>
                                        <a href="javascript:" title="Restart the host" class="status-off" id="<?php echo $host['id']; ?>">
                                            <span class="glyphicon glyphicon-off"></span>
                                        </a>
                                    <?php endif; ?>
                                </button>
                                <?php if ($host['host_guac_rdp_id']) : ?>
                                <button type="button" class="btn btn-default remote-rdp"
                                    data-remote-type="rdp"
                                    data-host-id="<?php echo $host['id']?>"
                                    data-host-name="<?php echo $host['host_desc']; ?>"
                                    data-host-server-ip="<?php echo $host['host_server_id']['server_ip']; ?>"
                                    data-guac-rdp="<?php echo $host['host_guac_rdp_id']; ?>"
                                    data-token="<?php echo $host['remote_token']; ?>">
                                    RDP
                                </button>
                                <?php endif;?>
                                <?php if ($host['host_guac_vnc_id']) : ?>
                                <button type="button" class="btn btn-default remote-vnc"
                                    data-remote-type="vnc"
                                    data-host-id="<?php echo $host['id']?>"
                                    data-host-name="<?php echo $host['host_desc']; ?>"
                                    data-host-server-ip="<?php echo $host['host_server_id']['server_ip']; ?>"
                                    data-guac-vnc="<?php echo $host['host_guac_vnc_id']; ?>"
                                    data-token="<?php echo $host['remote_token']; ?>">
                                    VNC
                                </button>
                                <?php endif;?>
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <?php echo $host['host_desc']; ?>
                                    <span class="glyphicon glyphicon-triangle-bottom" style="vertical-align:text-top;"></span>
                                </button>
                                <!--<a title="View Resource Usage" id="{{ id }}" style="float: right" class="btn btn-default btn viewStats" href="/hosts/stats/{{ id }}"><span class="glyphicon glyphicon-stats"></span></a>-->
                                <!--<a title="Add Port" id="{{ id }}" style="float: right" class="addPort" href="/ports/create/{{ id }}"><span class="glyphicon glyphicon-plus">Port </span></a>&nbsp;&nbsp;-->
                                <!--<a id="{{ id }}" style="float: right" class="btn btn-default btn viewPorts" href="/ports/index/{{ id }}"><span class="glyphicon glyphicon-file">Ports</span></a>-->

                                <ul class="dropdown-menu">
                                    <!-- Dropdown menu links -->
                                    <li>
                                        <a id="<?php echo $host['id']; ?>" class="license" href="/license/index/<?php echo $host['id']; ?>">
                                            <span class="glyphicon glyphicon-file"></span>&nbsp;Installation & Licensing
                                        </a>
                                    </li>
                                    <!--<li><a href="/backups/index/{{ id }}"><span class="glyphicon glyphicon-hdd"></span>&nbsp;Backups</a></li>-->
                                    <li>
                                        <a href="/ports/index/<?php echo $host['id']; ?>">
                                            <span class="glyphicon glyphicon-plane"></span>&nbsp;Open Ports
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:" class="confirm-push-host" id="<?php echo $host['id']; ?>">
                                            <span class="glyphicon glyphicon-upload"></span>&nbsp;Push Setup to Host
                                        </a>
                                    </li>
                                    <li>
                                        <a class="" href="/hosts/dashboard/<?php echo $host['id']; ?>">
                                            <span class="glyphicon glyphicon-info-sign"></span>&nbsp;System Info
                                        </a>
                                    </li>
                                    <li>
                                        <a class="" href="/hosts/network/<?php echo $host['id']; ?>">
                                            <span class="glyphicon glyphicon-hdd"></span>&nbsp;Network
                                        </a>
                                    </li>
                                    <!--<li><a href="/hosts/bandwidth/{{ id }}"><span class="glyphicon glyphicon-transfer"></span>&nbsp;Bandwidth Usage</a></li>-->
                                    <li>
                                        <a href="javascript:" class="confirm-delete" id="<?php echo $host['id']; ?>">
                                            <span class="glyphicon glyphicon-minus"></span>&nbsp;Remove Host
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:" class="confirm-reset-host" id="<?php echo $host['id']; ?>">
                                            <span class="glyphicon glyphicon-refresh"></span>&nbsp;Reset Connection
                                        </a>
                                    </li>
                                    <!--<li><a href="javascript:" class="confirm-restart-host" id="{{ id }}"><span class="glyphicon glyphicon-refresh"></span>&nbsp;Restart Host</a></li>-->
                                    {{ if user:group == "admin" || user:group == "beta"}}
                                        <hr>
                                        <li>
                                            &nbsp;&nbsp;<span class="glyphicon glyphicon-alert"></span><span style='color:#cc2828'>&nbsp;Beta Features&nbsp;</span>
                                        </li>
                                        <li>
                                            <a class="confirm-beta" href="/backups/index/<?php echo $host['id']; ?>">
                                                <span class="glyphicon glyphicon-cloud-upload"></span>&nbsp;Off-Site Backup
                                            </a>
                                        </li>
                                        <!--<li><a href="javascript:" class="confirm-beta" href="/hosts/invite_user/{{ id }}"><span class="glyphicon glyphicon-transfer"></span>&nbsp;Invite a User <span style="color: red; font-weight: italics">Beta Access</span></a></li>-->
                                        <li>
                                            <a class="confirm-beta" href="/reports/index/<?php echo $host['id']; ?>/aloha">
                                                <span class="glyphicon glyphicon-modal-window"></span>&nbsp;POS System
                                            </a>
                                            <ul>
                                                <li style="list-style-type:none">
                                                    <a class="confirm-beta" href="/reports/index/<?php echo $host['id']; ?>/aloha">
                                                        <span class="glyphicon glyphicon-calendar"></span>&nbsp;Reports <span style="font-size: 12px">(Daily/Weekly/Yearly)</span>
                                                    </a>
                                                </li>
                                                <li style="list-style-type:none">
                                                    <a class="" href="/reports/view_debug/<?php echo $host['id']; ?>/aloha">
                                                        <span class="glyphicon glyphicon-exclamation-sign"></span>&nbsp;Debug Reports <span style="font-size: 12px"></span>
                                                    </a>
                                                </li>
                                                <li style="list-style-type:none">
                                                    <a class="confirm-waitingtogrind" id="<?php echo $host['id']; ?>" href="">
                                                        <span class="glyphicon glyphicon-wrench"></span>&nbsp;Fix WaitingToGrind
                                                    </a>
                                                </li>
                                                <li style="list-style-type:none">
                                                    <a class="confirm-settlebatch" id="<?php echo $host['id']; ?>" href="/reports/settleBatch/<?php echo $host['id']; ?>">
                                                        <span class="glyphicon glyphicon-usd"></span>&nbsp;Settle Batch
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>

                                        <li>
                                            <a href="/hosts/assign_user/<?php echo $host['id']; ?>">
                                                <span class="glyphicon glyphicon-user"></span>&nbsp;Assign User
                                            </a>
                                        </li>

                                        <li>
                                            <a href="/hosts/assigned_users/<?php echo $host['id']; ?>">
                                                <span class="glyphicon glyphicon-user"></span>&nbsp;View Assigned Users
                                            </a>
                                        </li>

                                        <li>
                                            <!-- <a class="confirm-beta"> -->
                                            <div style="display:block;padding:3px 20px;">
                                                <span class="glyphicon glyphicon-transfer"></span>&nbsp;Connect
                                            </div>
                                            <!-- </a> -->
                                            <ul style="list-style:none;padding-left:20px;">
                                                <?php if (!$host['host_guac_rdp_id']) : ?>
                                                    <li class="host-dropdown">
                                                        <a href="javascript:" class="host-dropdown remote-create"
                                                            data-remote-type="rdp"
                                                            data-host-id="<?php echo $host['id']; ?>"
                                                            data-host-server-ip="<?php echo $host['host_server_id']['server_ip']; ?>"
                                                            data-host-name="<?php echo $host['host_desc']; ?>"
                                                            data-guac-rdp="<?php echo $host['host_guac_rdp_id']; ?>"
                                                            data-token="<?php echo $host['remote_token']; ?>">
                                                            <span class=""></span>&nbsp;
                                                            Create RDP
                                                        </a>
                                                    </li>
                                                <?php else : ?>
                                                    <li class="host-dropdown">
                                                        <a href="/hosts/logs/<?php echo $host['id']; ?>" class="host-dropdown rdp-logs"
                                                            data-remote-type="rdp"
                                                            data-host-id="<?php echo $host['id']; ?>"
                                                            data-host-server-ip="<?php echo $host['host_server_id']['server_ip']; ?>"
                                                            data-host-name="<?php echo $host['host_desc']; ?>"
                                                            data-guac-rdp="<?php echo $host['host_guac_rdp_id']; ?>"
                                                            data-token="<?php echo $host['remote_token']; ?>">
                                                            <span class=""></span>&nbsp;
                                                            View RDP Logs
                                                        </a>
                                                    </li>
                                                <?php endif; ?>

                                                <?php if (!$host['host_guac_vnc_id']) : ?>
                                                    <li>
                                                        <a href="javascript:" class="host-dropdown remote-create"
                                                            data-remote-type="vnc"
                                                            data-host-id="<?php echo $host['id']; ?>"
                                                            data-host-server-ip="<?php echo $host['host_server_id']['server_ip']; ?>"
                                                            data-host-name="<?php echo $host['host_desc']; ?>"
                                                            data-guac-vnc="<?php echo $host['host_guac_vnc_id']; ?>"
                                                            data-token="<?php echo $host['remote_token']; ?>">
                                                            <span class=""></span>&nbsp;
                                                            Create VNC
                                                        </a>
                                                    </li>
                                                <?php else : ?>
                                                    <li class="host-dropdown">
                                                        <a href="/hosts/logs/<?php echo $host['id'].'/'.$host['host_guac_vnc_id']; ?>" class="host-dropdown vnc-logs">
                                                            <span class=""></span>&nbsp;
                                                            View VNC Logs
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </li>

                                        {{ navigation:links group="coming-soon" }}
                                    {{ endif }}
                                    <!--<li><a href="/hosts/viewServices/{{ id }}"><span class="glyphicon glyphicon-eye-open"></span>&nbsp;Services & Tasks</a></li>
                                    <li><a href="/hosts/systemRes/{{ id }}"><span class="glyphicon glyphicon-stats"></span>&nbsp;System Resources</a></li>-->
                                    <!--<li><a href="/logging/{{ id }}"><span class="glyphicon glyphicon-warning-sign"></span>&nbsp;Debug Logs</a></li>-->
                                    <!--<li><a href="/hosts/restartHost/{{ id }}"><span class="glyphicon glyphicon-refresh"></span>&nbsp;Restart Host</a></li>-->
                                    <!-- <hr> -->
                                    {{ navigation:links group="nav-extra" }}
                                </ul>
                            </div>
                            <!--<br>{{ host_server_id.server_name }}-->
                            <!-- <br>Installation Key: {{ host_license.license_serial }}
                            <br>Install Status:{{ host_license.license_status.value }}-->
                        </td>

                        <td>
                            <?php echo $this->hosts_m->day($host['host_status_timestamp']); ?>
                            <br />
                            <?php echo $this->hosts_m->hour($host['host_status_timestamp']); ?>
                        </td>

                        <td>
                            <span style="font-size:10px">
                                <?php echo $host['host_server_id']['server_name']; ?>
                            </span>
                            <br>
                            <span id="{{ id }}" class="host-status glyphicon glyphicon-cloud" style="padding-right: 5px;border-radius:13px" title="Host Down"></span>
                            <i>
                                <?php echo $host['host_server_id']['server_ip']; ?>
                            </i>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- show user assigned streams-->
<?php if ($assigned_hosts) : ?>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h2>Assigned Hosts</h2>
            </div>
            <table class="table table-striped datatable">
                <thead>
                    <tr>
                        <th>
                            <a class="help" title="Assigned Host" description="This the descriptive name for the host.">Assigned Host</a>
                        </th>
                        <th>
                            <a class="help" title="Last Online" description="The host should report every 30-60 seconds or so. If it does not, you may see the last time it did.">Last Online</a>
                        </th>
                        <th>
                            <a class="help" title="IP Address" description="This will be the address you use to access your ports, and data.">IP Address</a>
                        </th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($assigned_hosts as $assigned) : ?>
                    <tr class="{{ odd_even }}">
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-default <?php if ($assigned['host_id']['permissions']->perm_restart) :
?>confirm-restart-host"<?php
endif; ?> title="Restart the host">
                                    <?php if ($assigned['host_id']['host_status_timestamp'] >= date('U', strtotime('-40 seconds'))) : ?>
                                        <a href="javascript:" title="Restart the host" class="status-on" id="<?php echo $assigned['host_id']['id']; ?>">
                                            <span class="glyphicon glyphicon-off"></span>
                                        </a>
                                    <?php else : ?>
                                        <a href="javascript:" title="Restart the host" class="status-off" id="<?php echo $assigned['host_id']['id']; ?>">
                                            <span class="glyphicon glyphicon-off"></span>
                                        </a>
                                    <?php endif; ?>
                                </button>
                                <?php if ($assigned['host_id']['host_guac_rdp_id'] && $assigned['host_id']['permissions']->perm_connect) : ?>
                                <button type="button" class="btn btn-default remote-rdp"
                                    data-remote-type="rdp"
                                    data-host-id="<?php echo $assigned['host_id']['id']?>"
                                    data-host-name="<?php echo $assigned['host_id']['host_desc']; ?>"
                                    data-host-server-ip="<?php echo $assigned['host_id']['server_ip']; ?>"
                                    data-guac-rdp="<?php echo $assigned['host_id']['host_guac_rdp_id']; ?>"
                                    data-token="<?php echo $assigned['host_id']['remote_token']; ?>">
                                    RDP
                                </button>
                                <?php endif;?>
                                <?php if ($assigned['host_id']['host_guac_vnc_id'] && $assigned['host_id']['permissions']->perm_connect) : ?>
                                <button type="button" class="btn btn-default remote-vnc"
                                    data-remote-type="vnc"
                                    data-host-id="<?php echo $assigned['host_id']['id']?>"
                                    data-host-name="<?php echo $assigned['host_id']['host_desc']; ?>"
                                    data-host-server-ip="<?php echo $assigned['host_id']['server_ip']; ?>"
                                    data-guac-vnc="<?php echo $assigned['host_id']['host_guac_vnc_id']; ?>"
                                    data-token="<?php echo $assigned['host_id']['remote_token']; ?>">
                                    VNC
                                </button>
                                <?php endif;?>
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <?php echo $assigned['host_id']['host_desc']; ?>
                                    <span class="glyphicon glyphicon-triangle-bottom" style="vertical-align:text-top;"></span>
                                </button>

                                <ul class="dropdown-menu">
                                    <!-- Dropdown menu links -->
                                    <li>
                                        <a id="<?php echo $assigned['host_id']['id']; ?>" class="license" href="/license/index/<?php echo $assigned['host_id']['id']; ?>">
                                            <span class="glyphicon glyphicon-file"></span>&nbsp;Installation & Licensing
                                        </a>
                                    </li>
                                    <!--<li><a href="/backups/index/{{ id }}"><span class="glyphicon glyphicon-hdd"></span>&nbsp;Backups</a></li>-->
                                    <?php if ($assigned['host_id']['permissions']->perm_ports) : ?>
                                    <li>
                                        <a href="/ports/index/<?php echo $assigned['host_id']['id']; ?>">
                                            <span class="glyphicon glyphicon-plane">
                                            </span>&nbsp;Open Ports
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <?php if ($assigned['host_id']['permissions']->perm_push) : ?>
                                    <li>
                                        <a href="javascript:" class="confirm-push-host" id="<?php echo $assigned['host_id']['id']; ?>">
                                            <span class="glyphicon glyphicon-upload"></span>&nbsp;Push Setup to Host
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <?php if ($assigned['host_id']['permissions']->perm_info) : ?>
                                    <li>
                                        <a class="" href="/hosts/dashboard/<?php echo $assigned['host_id']['id']; ?>">
                                            <span class="glyphicon glyphicon-hdd"></span>&nbsp;System Info
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <?php if ($assigned['host_id']['permissions']->perm_info) : ?>
                                    <li>
                                        <a class="" href="/hosts/network/<?php echo $assigned['host_id']['id']; ?>">
                                            <span class="glyphicon glyphicon-hdd"></span>&nbsp;Network
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <!--<li><a href="/hosts/bandwidth/{{ id }}"><span class="glyphicon glyphicon-transfer"></span>&nbsp;Bandwidth Usage</a></li>-->
                                    <?php if ($assigned['host_id']['permissions']->perm_remove) : ?>
                                    <li>
                                        <a href="javascript:" class="confirm-delete" id="<?php echo $assigned['host_id']['id']; ?>">
                                            <span class="glyphicon glyphicon-minus"></span>&nbsp;Remove Host
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <?php if ($assigned['host_id']['permissions']->perm_reset) : ?>
                                    <li>
                                        <a href="javascript:" class="confirm-reset-host" id="<?php echo $assigned['host_id']['id']; ?>">
                                            <span class="glyphicon glyphicon-refresh"></span>&nbsp;Reset Connection
                                        </a>
                                    </li>
                                    <?php endif; ?>

                                    {{ if user:group == "admin" || user:group == "beta"}}
                                        <hr>
                                        <li>
                                            <span style="color: red; font-weight: italics">Beta Access</span>
                                        </li>
                                        <?php if ($assigned['host_id']['permissions']->perm_backup) : ?>
                                        <li>
                                            <a class="confirm-beta" href="/backups/index/<?php echo $assigned['host_id']['id']; ?>">
                                                <span class="glyphicon glyphicon-hdd"></span>&nbsp;Off-Site Backup
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        <!--<li><a href="javascript:" class="confirm-beta" href="/hosts/invite_user/{{ id }}"><span class="glyphicon glyphicon-transfer"></span>&nbsp;Invite a User <span style="color: red; font-weight: italics">Beta Access</span></a></li>-->
                                        <?php if ($assigned['host_id']['permissions']->perm_reports) : ?>
                                        <li>
                                            <a class="confirm-beta" href="/reports/index/<?php echo $assigned['host_id']['id']; ?>/aloha">
                                                <span class="glyphicon glyphicon-modal-window"></span>&nbsp;POS System
                                            </a>
                                            <ul>
                                                <li style="list-style-type:none">
                                                    <a class="confirm-beta" href="/reports/index/<?php echo $assigned['host_id']['id']; ?>/aloha">
                                                        <span class="glyphicon glyphicon-calendar"></span>&nbsp;Reports <span style="font-size: 12px">(Daily/Weekly/Yearly)</span>
                                                    </a>
                                                </li>
                                                <li style="list-style-type:none">
                                                    <a class="confirm-waitingtogrind" id="<?php echo $assigned['host_id']['id']; ?>" href="">
                                                        <span class="glyphicon glyphicon-wrench"></span>&nbsp;Fix WaitingToGrind
                                                    </a>
                                                </li>
                                                <li style="list-style-type:none">
                                                    <a class="confirm-settlebatch" id="<?php echo $assigned['host_id']['id']; ?>" href="/reports/settleBatch/<?php echo $assigned['host_id']['id']; ?>">
                                                        <span class="glyphicon glyphicon-usd"></span>&nbsp;Settle Batch
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                        <?php endif; ?>

                                        {{ navigation:links group="coming-soon" }}
                                    {{ endif }}
                                    <!--<li><a href="/hosts/viewServices/{{ id }}"><span class="glyphicon glyphicon-eye-open"></span>&nbsp;Services & Tasks</a></li>
                                    <li><a href="/hosts/systemRes/{{ id }}"><span class="glyphicon glyphicon-stats"></span>&nbsp;System Resources</a></li>-->
                                    <!--<li><a href="/logging/{{ id }}"><span class="glyphicon glyphicon-warning-sign"></span>&nbsp;Debug Logs</a></li>-->
                                    <!--<li><a href="/hosts/restartHost/{{ id }}"><span class="glyphicon glyphicon-refresh"></span>&nbsp;Restart Host</a></li>-->
                                    {{ navigation:links group="nav-extra" }}
                                </ul>
                            </div>
                            <!--<div id="hostProgress" class="progress" style="width:102px; margin-top: 10px;">
                                <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100" style="width: 15%">
                                    <span class="sr-only">15% Complete</span>
                                </div>
                            </div>-->
                            <!--<br>{{ host_server_id.server_name }}-->
                            <!-- <br>Installation Key: {{ host_license.license_serial }}
                            <br>Install Status:{{ host_license.license_status.value }}-->
                        </td>
                        <td>
                            <?php echo $this->hosts_m->day($assigned['host_id']['host_status_timestamp']); ?>
                            <br />
                            <?php echo $this->hosts_m->hour($assigned['host_id']['host_status_timestamp']); ?>
                        </td>
                        <td> <!--replace the ip get with a plugin -->
                            <span style="font-size:10px">
                                <?php echo $this->streams->entries->get_entry($assigned['host_id']['host_server_id'], 'servers', 'servers')->server_name?>
                            </span>
                            <br>
                            <span id="<?php echo $assigned['host_id']['id']?>" class="host-status glyphicon glyphicon-cloud" style="padding-right: 5px;border-radius:13px" title="Host Down">
                            </span>
                            <i>
                                <?php echo $assigned['host_id']['server_ip']; ?>
                            </i>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot></tfoot>
            </table>
        </div>

        <?php endif; ?>

        {{ error }}{{ input }}
        {{ if error }}
            <div class="alert alert-danger" role="alert">
                <strong>Oh snap!</strong> There was a problem with something: {{ input }}
            </div>
        {{ endif }}
    {{ else }}
        <p><a href="users/login">Please login first.</a></p>
    {{ endif }}

    </div>
</div>

<!-- DIALOGS & POP FORMS ---->

<div id="addHostForm" class="dialog" style="display: none;" title="Create A Host">
    <div class="section host-create">
        {{ if user:logged_in }}
            {{ streams:form namespace="hosts" stream="hosts" limit="5" mode="new" return="hosts" exclude="host_status_timestamp|host_license|host_ssh_user|host_ssh_pass|host_ssh_port|host_group|host_status|host_info|host_server_id|host_guac_vnc_id|host_guac_rdp_id"}}
                {{ form_open }}
                    {{ fields }}
                        <label for="host_desc">Host Name:</label>
                        {{ input }}<br />
                    {{ /fields }}
                    <label for="host_server_id">Server:</label>
                    <br />
                    <select id='server' name='host_server_id'>
                        {{ servers }}
                            <option value="{{ id }}">{{ server_name }}</option>
                        {{ /servers }}
                    </select>
                    <br /><br />
                    {{ form_submit }}
                {{ form_close }}
                <p>{{ error }}{{ input }}</p>
            {{ /streams:form }}
            <p>{{ error }}{{ input }}</p>
        {{ else }}
            <p><a href="users/login">Please login first.</a></p>
        {{ endif }}
    </div>
    <script>
    $('#host_desc').attr('placeholder', 'Description of Host');
    </script>
</div>

<form id="create-vnc" class="" style="display:none">

    <input type="checkbox" name="push_vnc" id="push-vnc" style="display:inline-block;">
    <p style="display:inline-block;">
        <label for="push-vnc" style="display:inline-block;">Install VNC </label> (If you are unsure if another VNC server is installed, the port number should be at least 5902)
    </p>

    <label for="vnc-port">VNC Port: </label>
    <input type="number" name="vnc_port" id="vnc-port" max="65535" placeholder="5900" value="5900">

    <label for="vnc-password">VNC Password: </label>
    <input type="password" name="vnc_password" id="vnc-password" maxlength="8">
</form>

<form id="create-rdp" class="" style="display:none">
    <label for="rdp-port">Windows Username: </label>
    <input type="text" name="rdp_windows_username" id="rdp-windows-username" max="65535">

    <label for="rdp-password">Windows Password: </label>
    <input type="password" name="rdp_windows_password" id="rdp-windows-password">

    <label for="rdp-port">RDP Port: </label>
    <input type="number" name="rdp_port" id="rdp-port" max="65535" placeholder="3389" value="3389">
</form>

<input type="hidden" value="{{user:id}}" id="user-id">
