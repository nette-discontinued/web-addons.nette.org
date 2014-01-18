<?php

namespace NetteAddons;

use Nette,
	NetteAddons\Model\Addon;



/**
 * @author David Grudl
 * @author Jan Marek
 * @author Patrik Votoček
 * @author Jan Tvrdík
 */
class TextPreprocessor extends Nette\Object
{
	const FORMAT_TEXY = 'texy';
	const FORMAT_MARKDOWN = 'markdown';

	/** @var ITextProcessor[]|array */
	private $processors = array();

	/** @var Model\Utils\Licenses */
	private $licenses;


	public function __construct(Model\Utils\Licenses $licenses)
	{
		$this->licenses = $licenses;
	}



	/**
	 * @return TextPreprocessor
	 */
	public function addProcessor(ITextProcessor $processor, $format = self::FORMAT_TEXY)
	{
		$this->processors[$format] = $processor;
		return $this;
	}



	/**
	 * @param Model\Addon
	 * @return array
	 * @throws NotImplementedException
	 */
	public function processDescription(Addon $addon)
	{
		if (isset($this->processors[$addon->descriptionFormat])) {
			return $this->processors[$addon->descriptionFormat]->process($addon->description);
			return $this->processTexyContent($addon->description);
		} else {
			throw new \NetteAddons\NotImplementedException('Format "' . $addon->descriptionFormat . '" is not supported');
		}
	}



	/**
	 * @param  string|array
	 * @return \Nette\Utils\Html
	 */
	public function processLicenses($licenses)
	{
		if (is_string($licenses)) {
			$licenses = array_map('trim', explode(',', $licenses));
		}

		$container = \Nette\Utils\Html::el();
		foreach ($licenses as $license) {
			if (count($container->getChildren()) > 0) {
				$container->add(', ');
			}

			if ($this->licenses->isValid($license)) {
				$container->create('a', array(
					'href' => $this->licenses->getUrl($license),
					'title' => $this->licenses->getFullName($license),
				))->setText($license);
			} else {
				$container->add($license);
			}
		}
		return $container;
	}
}
