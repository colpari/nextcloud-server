<?php
/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */
namespace OCA\Federation\AppInfo;

use OCA\DAV\Events\SabrePluginAuthInitEvent;
use OCA\Federation\Listener\SabrePluginAuthInitListener;
use OCA\Federation\Middleware\AddServerMiddleware;
use OCA\Federation\TrustedServers;
use OCA\Files_Sharing\External\Manager;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\DB\Exception;

class Application extends App implements IBootstrap {

	/**
	 * @param array $urlParams
	 */
	public function __construct($urlParams = []) {
		parent::__construct('federation', $urlParams);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerMiddleware(AddServerMiddleware::class);

		$context->registerEventListener(SabrePluginAuthInitEvent::class, SabrePluginAuthInitListener::class);
	}

	public function boot(IBootContext $context): void {
		$context->injectFn([$this, 'autoAcceptShares']);
	}

	/**
	 * Accept all shares from trusted servers, where the “auto accept” flag is set to true/1.
	 *
	 * @param Manager $filesSharingManager the file sharing manager provides open shares and allows to accept them
	 * @param TrustedServers $trustedServers used to check if “auto accept” was enabled
	 * @return void
	 * @throws Exception
	 */
	public function autoAcceptShares(Manager $filesSharingManager, TrustedServers $trustedServers): void {
		$openShares = $filesSharingManager->getOpenShares();
		foreach ($openShares as $openShare) {
			if (isset($openShare['remote']) and isset($openShare['id'])) {
				$remoteAddress = $openShare['remote'];
				if ($trustedServers->isAutoAcceptEnabled($remoteAddress)) {
					$filesSharingManager->acceptShare($openShare['id']);
				};
			}
		}
	}
}
