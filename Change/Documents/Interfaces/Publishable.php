<?php
namespace Change\Documents\Interfaces;

/**
 * @name \Change\Documents\Interfaces\Publishable
 */
interface Publishable
{
	const STATUS_DRAFT = 'DRAFT';
	
	const STATUS_VALIDATION = 'VALIDATION';
	
	const STATUS_PUBLISHABLE = 'PUBLISHABLE';
	
	const STATUS_UNPUBLISHABLE = 'UNPUBLISHABLE';
	
	const STATUS_DEACTIVATED = 'DEACTIVATED';
	
	const STATUS_FILED = 'FILED';

	/**
	 * @api
	 * @return integer
	 */
	public function getId();

	/**
	 * @api
	 * @return string
	 */
	public function getPublicationStatus();
	
	/**
	 * @api
	 * @param string $publicationStatus
	 */
	public function setPublicationStatus($publicationStatus);
	
	/**
	 * @api
	 * @return string|null
	 */
	public function getStartPublication();
		
	/**
	 * @api
	 * @param string|null $startPublication
	 */
	public function setStartPublication($startPublication);
	
	/**
	 * @api
	 * @return string|null
	 */
	public function getEndPublication();
	
	/**
	 * @api
	 * @param string|null $endPublication
	 */
	public function setEndPublication($endPublication);

	/**
	 * @api
	 * @return \Change\Presentation\Interfaces\Section[]
	 */
	public function getPublicationSections();

	/**
	 * @api
	 * @param \Change\Presentation\Interfaces\Website $preferredWebsite
	 * @return \Change\Presentation\Interfaces\Section
	 */
	public function getDefaultSection(\Change\Presentation\Interfaces\Website $preferredWebsite = null);

	/**
	 * @api
	 * @return \Change\Documents\PublishableFunctions
	 */
	public function getPublishableFunctions();
}