<div id="trading-status">
    <span id="testing-status">
    <?php
    $dMode = ($config['live']) ? "<span style='color: #090;'><b>LIVE</b></span>" : "<span style='color: #F00;'><b>TESTING</b></span>";
    echo "Data Mode: {$dMode}";
    ?>
    </span> | <span id="client-active">
    <?php
    $cMode = ($client->isActive()) ? "<span style='color: #090;'><b>ACTIVE</b></span>" : "<span style='color: #F00;'><b>INACTIVE</b></span>";
    echo "Client Mode: {$cMode}";
    ?>
    </span> | <span id="client-trading">
    <?php
    $tMode = ($client->isTrading()) ? "<span style='color: #090;'><b>ACTIVE</b></span>" : "<span style='color: #F00;'><b>STANDBY</b></span>";
    echo "Trading Status: {$tMode}";
    ?>
    </span>
</div>