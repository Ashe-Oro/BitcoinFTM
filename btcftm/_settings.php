<link rel="stylesheet" href="css/settings.css" />
<script language="javascript" type="text/javascript" src="js/settings.js"></script>

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