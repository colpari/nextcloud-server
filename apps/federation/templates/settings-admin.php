<?php
/** @var array $_ */
use OCA\Federation\TrustedServers;
use OCP\Util;

/** @var \OCP\IL10N $l */
script('federation', 'settings-admin');
style('federation', 'settings-admin')
?>

<!-- TEMPLATE -->
<div style="display: none;">
	<div id="trusted-server-list-item">
		<?php $trustedServerListItemTemplate = <<<"EOD"
		<span class="status {status}"></span>
		<span class="trusted-server-name">
			{url}
		</span>
		<input name="accept-check-{id}"
			   id="trusted-server-accept-check-{id}"
			   class="checkbox auto-accept"
			   type="checkbox" value="1" {checked}>
		<label for="trusted-server-accept-check-{id}">
			{autoAcceptLabel}
		</label>
		<span class="icon icon-delete"></span>
		EOD;
		echo str_replace('{autoAcceptLabel}', Util::sanitizeHTML($l->t('auto-accept shares')), $trustedServerListItemTemplate); ?>
	</div>
</div>
<!-- TEMPLATE END -->

<div id="ocFederationSettings" class="section">
	<h2><?php p($l->t('Trusted servers')); ?></h2>
	<p class="settings-hint"><?php p($l->t('Federation allows you to connect with other trusted servers to exchange the user directory. For example this will be used to auto-complete external users for federated sharing. It is not necessary to add a server as trusted server in order to create a federated share.')); ?></p>
	<p class="settings-hint"><span class="icon icon-alert-outline"></span><?php p($l->t('Check the „auto-accept shares“ option only for servers which are part of your organization. Be sure not to introduce any security risks for your users.')); ?></p>

	<ul id="listOfTrustedServers">
		<?php foreach ($_['trustedServers'] as $trustedServer) { ?>
			<li id="<?php p($trustedServer['id']); ?>">
				<?php
				$status = 'error';
				if ((int)$trustedServer['status'] === TrustedServers::STATUS_OK) {
					$status = 'success';
				} elseif (
					(int)$trustedServer['status'] === TrustedServers::STATUS_PENDING ||
					(int)$trustedServer['status'] === TrustedServers::STATUS_ACCESS_REVOKED
				) {
					$status = 'indeterminate';
				}
				$checked = ($trustedServer['auto_accept'] === 1) ? "checked" :
					"";
				echo str_replace(['{status}', '{url}', '{id}', '{checked}', '{autoAcceptLabel}'],
					[
						$status,
						Util::sanitizeHTML($trustedServer['url']),
						$trustedServer['id'],
						$checked,
						Util::sanitizeHTML($l->t('auto-accept shares')),
					],
					$trustedServerListItemTemplate);
				?>
			</li>
		<?php } ?>
	</ul>
	<p id="ocFederationAddServer">
		<button id="ocFederationAddServerButton" class=""><?php p($l->t('+ Add trusted server')); ?></button>
		<input id="serverUrl" class="hidden" type="text" value="" placeholder="<?php p($l->t('Trusted server')); ?>" name="server_url"/>
		<button id="ocFederationSubmit" class="hidden"><?php p($l->t('Add')); ?></button>
		<span class="msg"></span>
	</p>

</div>
