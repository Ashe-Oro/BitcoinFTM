
<div id="best_ops">

  <?php $full = $matrix ?>
  <div id="best_ops-matrix">

  <table>
  <tr>
  <th class="cur-bg-usd" rowspan="2">ASK MARKETS</th>
  <th class="cur-bg-btc" style="text-align: center;" colspan="<?php echo count($full); ?>">BID MARKETS</th>
  </tr>
  <tr>
  <?php
  foreach($full as $askmarket => $mx){
    echo "<th class='mkt-bg-{$askmarket}'>".sanitizeMarketName($askmarket)."</th>";
  }
  ?>
  </tr>

<?php
$fclone = $full;
foreach($full as $askmarket => $mx){
  $aname = sanitizeMarketName($askmarket);
  echo "<tr>";
  echo "<th class='mkt-bg-dark1-{$aname}'>{$aname}</th>";
  foreach($fclone as $bidmarket => $mx2){
    $bname = sanitizeMarketName($bidmarket);
    $m = isset($mx[$bidmarket]) ? $mx[$bidmarket] : NULL;
    if (!$m || $aname == $bname) {
      echo "<td class='matrix-cell'><span class='matrix-cell-value'>----</span><span class='matrix-cell-perc'></span></td>";
    } else {
      //$class = ($m['profit'] > 0) ? 'pos' : 'neg';
      echo "<td class='matrix-cell' id='best_ops-{$aname}-{$bname}' data-ask='{$aname}' data-bid='{$bname}'><span class='matrix-cell-value'>...</span><span class='matrix-cell-vol'></span><span class='matrix-cell-perc'></span></td>";
    }
  }
  echo "</tr>";
}

//var_dump($full);
?>
</table>
  </div>
<h3>Click any highlighted Arbitrage Opportunity above to begin Arbitrage!</h3>
</div>