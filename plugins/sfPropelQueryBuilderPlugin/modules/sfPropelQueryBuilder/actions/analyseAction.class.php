<?php
class analyseAction extends sfAction
{
	public function execute()
	{
		// If this is not a POST then redirect
		if ($this->getRequest()->getMethod() != sfRequest::POST)
		{
			$this->forward($this->getModuleName(), 'index');
		}

		// Get the input, moan and exit if there's nothing
		$this->input = trim($this->getRequestParameter('logic'));
		if ($this->input == '')
		{
			$this->getRequest()->setError('error', "Can't analyse nothing - try again!");
		}

		// Get some options
		$comments = $this->getRequestParameter('code_comments');
		$comments = isset($comments[0]);
		$concat = $this->getRequestParameter('concat_objects');
		$concat = isset($concat[0]);
		$showTree = $this->getRequestParameter('show_tree');
		$showTree = isset($showTree[0]);
		$type = $this->getRequestParameter('return_type');
		$loop = $this->getRequestParameter('demo_loop');
		$loop = isset($loop[0]);
		$resultSetType = $this->getRequestParameter('resultset_type');

		// Set the return type
		if ($type != CriteriaBuilder::RETURN_ARRAY)
		{
			$type = CriteriaBuilder::RETURN_RESULTSET;
		}

		// Set the ResultSet
		if ($resultSetType != CriteriaBuilder::RESULTSET_NUM)
		{
			$resultSetType = CriteriaBuilder::RESULTSET_ASSOC;
		}

		$cPrefix = $this->getRequestParameter('crit_prefix');

		// Set tree to nothing, in case error occurs within analyser
		$tree = null;

		try
		{
			$analyser = new CriteriaAnalyse();
			$tree = $analyser->analyse($this->input);
			$analyser = null;
		}
		catch (Exception $e)
		{
			$this->getRequest()->setError('error', $e->getMessage());
		}

		if (!$this->getRequest()->hasErrors())
		{
			$builder = new CriteriaBuilder($cPrefix);
			$builder->comments = $comments;
			$builder->loop = $loop;
			$builder->concat = $concat;
			$builder->returnType = $type;
			$builder->resultSetType = $resultSetType;

			try
			{
				$code = $builder->build($tree);
			}
			catch (Exception $e)
			{
				$this->getRequest()->setError('error', $e->getMessage());
			}
		}

			if ($showTree)
			{
				$this->tree = print_r($tree, true);
			}
			else
			{
				$this->tree = null;
			}

		if (!$this->getRequest()->hasErrors())
		{
			$this->code = print_r($code, true);
			$builder = null;
	
			// Execute code
			if (false)
			{
				eval($code);
			}
		}

		$this->setTemplate('index');
		return sfView::SUCCESS;
	}
}
