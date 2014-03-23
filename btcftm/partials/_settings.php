<h1>Account Settings</h1>

<fieldset>
<legend>Personal Information</legend>
<form name="personal-info-form" id="personal-info-form" method="POST">
<table>
<tr>
<td><label for="client-firstname">First Name: </label></td>
<td><input type="text" id="client-firstname" size="20" maxlength="32" value="<?php echo $client->getFirstName(); ?>" /></td>
</tr>
<tr>
<td><label for="client-lastname">Last Name: </label></td>
<td><input type="text" id="client-lastname" size="20" maxlength="32" value="<?php echo $client->getLastName(); ?>" /></td>
</tr>
<tr>
<td><label for="client-username">Username: </label></td>
<td><input type="text" id="client-username" size="20" maxlength="32" value="<?php echo $client->getUsername(); ?>" /></td>
</tr>
<tr>
<td colspan="2"><input type="submit" id="client-submit" value="Update Account Information" /></td>
</tr>
</table>
</form>
</fieldset>

<fieldset>
<legend>Password</legend>
<form name="password" id="password-form" method="POST">
<table>
<tr>
<td><label for="client-old-pwd">Old Password:</label></td>
<td><input type="text" id="client-old-pwd" size="20" maxlength="32" value="" /></td>
</tr>
<tr>
<td><label for="client-new-pwd">New Password:</label></td>
<td><input type="text" id="client-new-pwd" size="20" maxlength="32" value="" /></td>
</tr>
<tr>
<td><label for="client-new-pwd2">New Password (again):</label></td>
<td><input type="text" id="client-new-pwd2" size="20" maxlength="32" value="" /></td>
</tr>
<tr>
<td colspan="2"><input type="submit" id="client-submit" value="Update Password" /></td>
</tr>
</table>
</fieldset>

<table>
<tr>
<th>Market</th><th>API Info</th><th>Balances</th><th>Add Funds</th></tr>
<?php
$curlist = $currencies->getCurrencyList();
foreach($markets as $mkt) {
  $pmarket = $client->getPrivateMarket($mkt->mname);
  $key  = ($pmarket) ? $pmarket->getAPIKey() : "";
  $secret = ($pmarket) ? $pmarket->getAPISecret() : "";

  echo "<tr id='portfolio-{$mkt->mname}'>";
  echo "<td class='portfolio-market'><h3>{$mkt->mname}</h3></td>";
  echo "<td class='portfolio-api-data'>";
  echo "<div class='portfolio-key'>API Key: <span class='portfolio-key-val'>{$key}</span></div>";
  echo "<div class='portfolio-secret'>API Secret: <span class='portfolio-key-secret'>{$secret}</span></div>";
  echo "</td>";

  echo "<td class='portfolio-balances'>";
  foreach($curlist as $abbr => $cur) {
    if ($mkt->supports($abbr)){
      $c = ($pmarket) ? $currencies->printCurrency($pmarket->getBalance($abbr), $abbr) : $currencies->printCurrency(0, $abbr);
      echo "<div class='portfolio-{$abbr}'>{$abbr}: <span class='portfolio-value' id='portfolio-value-{$mkt->mname}'>{$c}</span></div>";
    }
  }
  echo "</td>";

  echo "<td class='portfolio-finance'>";
  foreach($curlist as $abbr => $cur) {
    if ($mkt->supports($abbr)){
      echo "<div class='portfolio-{$abbr}'><input type='button' name='finance-portfolio-{$abbr}' id='finance-portfolio-{$abbr}-{$mkt->mname}' value='Add {$abbr} at {$mkt->mname}' /></div>";
    }
  }
  echo "</td>";
  echo "</tr>";
}
?>
</table>

<div id="portfolio-edit">
  <div class='portfolio-key'><input type='text' size='40' maxlength='40' value='' /></div>
  <div class='portfolio-secret'><input type='text' size='40' maxlength='40' value='' /></div>
  <div class='portfolio-update'><input type='submit' value='Update' /></div>
</div>