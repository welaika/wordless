<?php
/**
 *	extension file for SimpleTest
 *  @package        SimpleTest
 *  @subpackage     Extensions
 *	@version	$Id$
 */

/**
 * include SimpleTest Scorer class file
 */
require_once(dirname(__FILE__) . '/../../scorer.php');

/**
 * Collects SimpleReporter messages and constructs a
 * TreemapNode graph.
 *
 *  @package        SimpleTest
 *  @subpackage     Extensions
 */
class TreemapRecorder extends SimpleReporter
{
    public $_graph;
    public $_stack;
    public $_title;

    public function TreemapRecorder()
    {
        $this->SimpleReporter();
        $this->_stack = new TreemapStack();
        $this->_graph = null;
    }

    /**
     * returns a reference to the root node of the
     * collected treemap graph
     */
    public function getGraph()
    {
        return $this->_graph;
    }
    
    /**
     * is this test run finished?
     */
    public function isComplete()
    {
        return ($this->_graph != null);
    }
    
    /**
     * returns the title of the test
     */
    public function getTitle()
    {
        return $this->_title;
    }
    
    /**
     * stashes the title of the test
     */
    public function paintHeader($title)
    {
        $this->_title = $title;
    }
    
    public function paintFormattedMessage()
    {
    }
    
    /**
     * acceptor for start of test group node
     */
    public function paintGroupStart($message, $size)
    {
        parent::paintGroupStart($message, $size);
        $node = new TreemapNode("Group", $message);
        $this->_stack->push($node);
    }
    
    /**
     * acceptor for start of test case node
     */
    public function paintCaseStart($message)
    {
        parent::paintCaseStart($message);
        $node = new TreemapNode("TestCase", $message);
        $this->_stack->push($node);
    }
    
    /**
     * acceptor for start of test method node
     */
    public function paintMethodStart($message)
    {
        parent::paintMethodStart($message);
        $node = new TreemapNode("Method", $message);
        $this->_stack->push($node);
    }

    /**
     * acceptor for passing assertion node
     */
    public function paintPass($message)
    {
        parent::paintPass($message);
        $node = new TreemapNode("Assertion", $message, true);
        $current = $this->_stack->peek();
        if ($current) {
            $current->putChild($node);
        } else {
            echo "no current node";
        }
    }

    
    /**
     * acceptor for failing assertion node
     */

    public function paintFail($message)
    {
        parent::paintFail($message);
        $node = new TreemapNode("Assertion", $message, false);
        $current = $this->_stack->peek();
        $current->putChild($node);
        $current->fail();
    }

    /**
     * acceptor for end of method node
     */
    public function paintMethodEnd($message)
    {
        parent::paintCaseEnd($message);
        $node = $this->_stack->pop();
        $current = $this->_stack->peek();
        if ($node->isFailed()) {
            $current->fail();
        }
        $current->putChild($node);
    }

    /**
     * acceptor for end of test case
     */
    public function paintCaseEnd($message)
    {
        parent::paintCaseEnd($message);
        $node = $this->_stack->pop();
        $current = $this->_stack->peek();
        if ($node->isFailed()) {
            $current->fail();
        }
        $current->putChild($node);
    }
    
    /**
     * acceptor for end of test group. final group
     * pops the collected treemap nodes and assigns
     * it to the internal graph property.
     */
    public function paintGroupEnd($message)
    {
        $node = $this->_stack->pop();
        $current = $this->_stack->peek();
        if ($current) {
            if ($node->isFailed()) {
                $current->fail();
            }
            $current->putChild($node);
        } else {
            $this->_graph = $node;
        }
        parent::paintGroupEnd($message);
    }
}

/**
 * Creates a treemap graph, representing
 * each node in a test visualization.
 *
 *  @package        SimpleTest
 *  @subpackage     Extensions
 */
class TreemapNode
{
    public $_name;
    public $_description;
    public $_status;
    public $_parent;
    public $_size;
    
    public function TreemapNode($name, $description, $status=true)
    {
        $this->_name = $name;
        $this->_description = $description;
        $this->_status = $status;
        $this->_children = array();
    }
    
    /**
     * @return string label of this node
     */
    public function getName()
    {
        return $this->_name;
    }
    
    /**
     * @return string description of this node
     */
    public function getDescription()
    {
        return $this->_description;
    }
    
    /**
     * @return string status class string
     */
    public function getStatus()
    {
        return ($this->_status) ? "pass" : "fail";
    }
    
    /** 
     * Return list of child nodes from direct edges.
     */
    public function getChildren()
    {
        @uksort($this->_new_children, array($this, 'compareChildren'));
        return $this->_children;
    }

    /**
     * Comparator method to rank child nodes by total weight.
     */
    public function compareChildren($a, $b)
    {
        if ($this->_children[$a]->getTotalSize() > $this->_children[$b]->getTotalSize()) {
            $node_a = $this->_children[$a];
            $node_b = $this->_children[$b];
            $this->_children[$a] = $node_b;
            $this->_children[$b] = $node_a;
        }
    }
    
    /** 
     * Gets the number of immediate child edges from this node.
     */
    public function getSize()
    {
        return count($this->_children);
    }
    
    /** 
     * depth first search to get the total number of nodes 
     * that are descendants of this node.
     */
    public function getTotalSize()
    {
        if (!isset($this->_size)) {
            $size = $this->getSize();
            if (!$this->isLeaf()) {
                foreach ($this->getChildren() as $child) {
                    $size += $child->getTotalSize();
                }
            }
            $this->_size = $size;
        }
        return $this->_size;
    }
    
    /**
     * Fail this node.
     * @return void
     */
    public function fail()
    {
        $this->_status = false;
    }
    
    /** Is this node failed? */
    public function isFailed()
    {
        return ($this->_status == false);
    }
    
    /** Add an edge to a child node */
    public function putChild($node)
    {
        $this->_children[] = $node;
    }
    
    /** Is this node a leaf node? */
    public function isLeaf()
    {
        return (count($this->_children) == 0);
    }
}

/**
 * provides LIFO stack semantics
 *
 *  @package        SimpleTest
 *  @subpackage     Extensions
 */
class TreemapStack
{
    public $_list;

    public function TreemapStack()
    {
        $this->_list = array();
    }

    /**
     * Push an element onto the stack.
     */
    public function push($node)
    {
        $this->_list[] = $node;
    }
    
    /**
     * Number of elements in the stack.
     */
    public function size()
    {
        return count($this->_list);
    }
    
    /**
     * Take a peek at the top element on the
     * stack.
     */
    public function peek()
    {
        return end($this->_list);
    }
    
    /**
     * Pops an element off the stack.
     */
    public function pop()
    {
        return array_pop($this->_list);
    }
}
