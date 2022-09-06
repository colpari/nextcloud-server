<?php
/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Bjoern Schiessle <bjoern@schiessle.org>
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Morris Jobke <hey@morrisjobke.de>
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
namespace OCA\Federation\Controller;

use Exception;
use OCA\Federation\TrustedServers;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\HintException;
use OCP\IL10N;
use OCP\IRequest;

class SettingsController extends Controller {
	private IL10N $l;
	private TrustedServers $trustedServers;

	public function __construct(string $AppName,
								IRequest $request,
								IL10N $l10n,
								TrustedServers $trustedServers
	) {
		parent::__construct($AppName, $request);
		$this->l = $l10n;
		$this->trustedServers = $trustedServers;
	}


	/**
	 * Add server to the list of trusted Nextclouds.
	 *
	 * @AuthorizedAdminSetting(settings=OCA\Federation\Settings\Admin)
	 * @throws HintException
	 */
	public function addServer(string $url): DataResponse {
		$this->checkServer($url);
		$id = $this->trustedServers->addServer($url);

		return new DataResponse([
			'url' => $url,
			'id' => $id,
			'message' => $this->l->t('Added to the list of trusted servers')
		]);
	}

	/**
	 * Remove server from the list of trusted Nextclouds.
	 *
	 * @AuthorizedAdminSetting(settings=OCA\Federation\Settings\Admin)
	 */
	public function removeServer(int $id): DataResponse {
		$this->trustedServers->removeServer($id);
		return new DataResponse();
	}

	/**
	 * Update a property of a trusted Nextcloud.
	 *
	 * @AuthorizedAdminSetting(settings=OCA\Federation\Settings\Admin)
	 * @throws Exception
	 */
	public function updateServer(int $id, string $autoAccept): DataResponse {
		$serverUrl = $this->trustedServers->updateServer(
			$id,
			$autoAccept === 'true'
		);
		if ($autoAccept === 'true') {
			$message = $this->l->t(
				'Shares from %1$s will now be accepted automatically.',
				[$serverUrl]
			);
		} else {
			$message = $this->l->t(
				'Shares from %1$s will NOT be accepted automatically.',
				[$serverUrl]
			);
		}
		return new DataResponse([
			'message' => $message,
		]);
	}

	/**
	 * Check if the server should be added to the list of trusted servers or not.
	 *
	 * @AuthorizedAdminSetting(settings=OCA\Federation\Settings\Admin)
	 * @throws HintException
	 */
	protected function checkServer(string $url): bool {
		if ($this->trustedServers->isTrustedServer($url) === true) {
			$message = 'Server is already in the list of trusted servers.';
			$hint = $this->l->t('Server is already in the list of trusted servers.');
			throw new HintException($message, $hint);
		}

		if ($this->trustedServers->isNextcloudServer($url) === false) {
			$message = 'No server to federate with found';
			$hint = $this->l->t('No server to federate with found');
			throw new HintException($message, $hint);
		}

		return true;
	}
}
