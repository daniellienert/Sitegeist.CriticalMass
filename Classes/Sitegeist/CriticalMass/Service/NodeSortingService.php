<?php
namespace Sitegeist\CriticalMass\Service;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Eel\Utility as EelUtility;
use TYPO3\Eel\CompilingEvaluator;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

/**
 * @Flow\Scope("singleton")
 */
class NodeSortingService
{
	/**
     * @Flow\Inject(lazy=FALSE)
     * @var CompilingEvaluator
     */
    protected $eelEvaluator;

	/**
     * @Flow\InjectConfiguration(path="defaultContext", package="TYPO3.TypoScript")
     * @var array
     */
    protected $defaultTypoScriptContextConfiguration;

	/**
	 * @param NodeInterface $node
	 * @param string $eelExpression
	 * @param string $nodeTypeFilter
	 * @return void
	 */
	public function sortChildNodesByEelExpression(
		NodeInterface $node,
		$eelExpression,
		$nodeTypeFilter = 'TYPO3.Neos:Document'
	) {
		$nodes = $node->getChildNodes($nodeTypeFilter);

		foreach ($nodes as $subject) {

			$object = null;
			foreach ($nodes as $node) {
				if ($this->sortingConditionApplies($subject, $node)) {
					$object = $node;
					break;
				}
			}

			if ($object) {
				$subject->moveBefore($object);
			}
		}
	}

	/**
	 * @param NodeInterface $a
	 * @param NodeInterface $b
	 * @param string $eelExpression
	 * @return boolean
	 */
	protected function sortingConditionApplies(NodeInterface $a, NodeInterface $b, $eelExpression)
	{
		return EelUtility::evaluateEelExpression(
			$eelExpression,
			$this->eelEvaluator,
			['a' => $a, 'b' => $b],
			$this->defaultTypoScriptContextConfiguration
		);
	}
}
