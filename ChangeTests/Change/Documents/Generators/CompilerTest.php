<?php
namespace ChangeTests\Documents;

/**
 * @name \ChangeTests\Documents\Generators\CompilerTest
 */
class CompilerTest extends \ChangeTests\Change\TestAssets\TestCase
{
	public function testConstruct()
	{
		$compiler = new \Change\Documents\Generators\Compiler($this->getApplication(), $this->getApplicationServices());
		$this->assertCount(0, $compiler->getModels());
	}
	
	public function testLoadDocument()
	{
		$compiler = new \Change\Documents\Generators\Compiler($this->getApplication(), $this->getApplicationServices());
		$definitionPath = __DIR__ . '/TestAssets/TestType.xml';
		$model = $compiler->loadDocument('Change', 'Test', 'TestType', $definitionPath);
		$this->assertEquals('Change_Test_TestType', $model->getName());
		$this->assertCount(3, $compiler->getModels());
		
		$this->setExpectedException('\RuntimeException', 'Unable to load document definition');
		$compiler->loadDocument('Change', 'test', 'test1',  __DIR__ . '/TestAssets/notfound');
	}
	
	public function testCheckExtends()
	{
		$compiler = new \Change\Documents\Generators\Compiler($this->getApplication(), $this->getApplicationServices());
		
		$definitionPath = __DIR__ . '/TestAssets/TestType.xml';
		$m1 = $compiler->loadDocument('Change', 'Test', 'TestType', $definitionPath);
		
		$definitionPath = __DIR__ . '/TestAssets/TestTypeExt.xml';
		$m2 = $compiler->loadDocument('Change', 'Test', 'TestTypeExt', $definitionPath);
		
		
		$definitionPath = __DIR__ . '/TestAssets/TestTypeRepl.xml';
		$m3 = $compiler->loadDocument('Change', 'Test', 'TestTypeRepl', $definitionPath);
		
		$this->assertCount(5, $compiler->getModels());
		$this->assertCount(0, $compiler->getRootModelNames());

		$userModel = new \Change\Documents\Generators\Model('Rbs', 'User', 'User');
		$compiler->addModel($userModel);
		
		$compiler->buildTree();
		$compiler->validateInheritance();

		$this->assertSame($m1, $m3->getParent());
		$this->assertSame($m3, $m2->getParent());

		$this->assertCount(2, $compiler->getRootModelNames());
		
		$p1 = $m1->getPropertyByName('int1');
		$this->assertCount(0, $p1->getAncestors());
		
		$p2 = $m2->getPropertyByName('int1');
		$this->assertCount(2, $p2->getAncestors());
		
		$p3 = $m3->getPropertyByName('int1');
		$this->assertCount(1, $p3->getAncestors());
		
		
		$compiler->saveModelsPHPCode();
		
		$compilationPath = $this->getApplication()->getWorkspace()->compilationPath();
		$commonPath  = $compilationPath . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $m1->getNameSpace()). DIRECTORY_SEPARATOR;
		$this->assertFileExists($commonPath . $m2->getShortModelClassName() . '.php');
		$this->assertFileExists($commonPath . $m2->getShortBaseDocumentClassName() . '.php');
		$this->assertFileExists($commonPath . $m2->getShortDocumentLocalizedClassName() . '.php');
	}
}
