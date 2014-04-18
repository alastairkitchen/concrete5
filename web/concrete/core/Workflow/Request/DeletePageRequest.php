<?
namespace Concrete\Core\Workflow\Request;
use Workflow;
use Loader;
use Page;
use \Concrete\Core\Workflow\Description as WorkflowDescription;
use Permissions;
use PermissionKey;
use \Concrete\Core\Workflow\Progress\Progress as WorkflowProgress;
use CollectionVersion;
use Events;
use \Concrete\Core\Workflow\Progress\Action\Action as WorkflowProgressAction;
use \Concrete\Core\Workflow\Progress\Response as WorkflowProgressResponse;
class DeletePageRequest extends PageRequest {
	
	protected $wrStatusNum = 100;

	public function __construct() {
		$pk = PermissionKey::getByHandle('delete_page');
		parent::__construct($pk);
	}

	public function getWorkflowRequestDescriptionObject() {
		$d = new WorkflowDescription();
		$c = Page::getByID($this->cID, 'ACTIVE');
		$item = t('page');
		if ($c->getPageTypeHandle() == STACKS_PAGE_TYPE) {
			$item = t('stack');
		}
		$link = Loader::helper('navigation')->getLinkToCollection($c, true);
		$d->setEmailDescription(t("\"%s\" has been marked for deletion. View the page here: %s.", $c->getCollectionName(), $link));
		$d->setInContextDescription(t("This %s has been marked for deletion. ", $item));
		$d->setDescription(t("<a href=\"%s\">%s</a> has been marked for deletion. ", $link, $c->getCollectionName()));
		$d->setShortStatus(t("Pending Delete"));
		return $d;
	}
	
	public function getWorkflowRequestStyleClass() {
		return 'danger';
	}
	
	public function getWorkflowRequestApproveButtonClass() {
		return 'btn-danger';
	}

	public function getWorkflowRequestApproveButtonInnerButtonRightHTML() {
		return '<i class="icon-white icon-trash"></i>';
	}	
	
	public function getWorkflowRequestApproveButtonText() {
		return t('Approve Delete');
	}

	public function approve(WorkflowProgress $wp) {
		$c = Page::getByID($this->getRequestedPageID());
		if ($c->getPageTypeHandle() == STACKS_PAGE_TYPE) {
			$c = Stack::getByID($this->getRequestedPageID());
			$c->delete();
			$wpr = new WorkflowProgressResponse();
			$wpr->setWorkflowProgressResponseURL(View::url('/dashboard/blocks/stacks', 'stack_deleted'));
			return $wpr;
		}

		$cParentID = $c->getCollectionParentID();
		if (ENABLE_TRASH_CAN) {
			$c->moveToTrash();
		} else {
			$c->delete();
		}
		$wpr = new WorkflowProgressResponse();
		$wpr->setWorkflowProgressResponseURL(BASE_URL . DIR_REL . '/' . DISPATCHER_FILENAME . '?cID=' . $cParentID);
		return $wpr;
	}

	
}