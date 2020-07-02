<style>.bold{ font-weight: bold;}</style>
<div id="body" class="section">
    {{ if user:logged_in }}
    <div class="panel panel-default">
        <div class="panel-heading"><h2>{{ template:title }}
            <a style="float: right" id="refreshBtn" class="btn btn-default btn" href="/hosts/resetService/<?php echo $host->id ?>">
           <span class="glyphicon glyphicon-arrow-left">Refresh Info</span></a>
           <a style="float: right" id="backBtn" class="btn btn-default btn" onclick="window.history.go('-1')">
           <span class="glyphicon glyphicon-arrow-left">Back</span></a></h2></div>
        <table>
            <?php $data = json_decode( html_entity_decode( $host->host_info )  );
            foreach( $data as $row ){
                if( !is_array($row) ){
                    echo "<td>" . $row . "</td> ";
                }
            }
            echo "<tr>";

                    foreach( $data->NetworkConfiguration as $row ){
                        echo "<tr>";
                        echo "<td>
                        <span class='bold'>Device:</span> $row->Description<br />
                        <span class='bold'>Adapter Name:</span> $row->Caption
                        </td>";
                        echo "<td> <span class='bold'>IP:</span> ".
                        $row->IPAddress[0]
                        ."</td>";
                        echo "<td> <span class='bold'>Subnet:</span> ".
                        $row->IPSubnet[0]
                        . "</td>";
                        echo "</tr>";
                    }
                    echo "</tr>";

            foreach( $data->Hotfixes as $row ){
                    echo "<tr>";
                        echo "<td><span class='bold'>Hotfix:</span>" . $row->HotFixId . "</td> ";
                        echo "<td><span class='bold'>Installed:</span>" . $row->InstalledOn . "</td> ";
                    echo "</tr>";
                }
            ?>
        </table>
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
