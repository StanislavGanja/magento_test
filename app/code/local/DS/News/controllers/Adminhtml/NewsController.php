<?php

class DS_News_Adminhtml_NewsController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('ds_news');

        $grid = Mage::getModel('sales_resource/order_grid_collection');
        $exist =  method_exists($grid,'initTesting');
        $contentBlock = $this->getLayout()->createBlock('dsnews/adminhtml_news');
        $this->_addContent($contentBlock);
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        Mage::register('current_news', Mage::getModel('dsnews/news')->load($id));

        $this->loadLayout()->_setActiveMenu('dsnews');
        $this->_addContent($this->getLayout()->createBlock('dsnews/adminhtml_news_edit'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            try {
                $model = Mage::getModel('dsnews/news');
                $model->setData($data)->setId($this->getRequest()->getParam('id'));
                if (!$model->getCreated()) {
                    $model->setCreated(now());
                }
                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('News was saved successfully'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array(
                    'id' => $this->getRequest()->getParam('id')
                ));
            }
            return;
        }
        Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                Mage::getModel('dsnews/news')->setId($id)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('News was deleted successfully'));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $id));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $news = $this->getRequest()->getParam('news', null);
        if (is_array($news) && sizeof($news) > 0) {
            try {
                foreach ($news as $id) {
                    Mage::getModel('dsnews/news')->setId($id)->delete();
                }
                $this->_getSession()->addSuccess($this->__('Total of %d news have been deleted', sizeof($news)));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        } else {
            $this->_getSession()->addError($this->__('Please select news'));
        }
        $this->_redirect('*/*');
    }

    public function massEnableAction()
    {
        $news = $this->getRequest()->getParam('news', null);
        if (is_array($news) && sizeof($news) > 0) {
            try {
                $counter = 0;
                $news_model = Mage::getModel('dsnews/news');
                foreach ($news as $id) {
                    $new = $news_model->load($id);
                    if (!$new->getStatus()) {
                        $new->setStatus(1);
                        $new->save();
                        $counter++;
                    }
                }
                $this->_getSession()->addSuccess($this->__('Total of %d news have been Enable', $counter));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        } else {
            $this->_getSession()->addError($this->__('Please select news'));
        }
        $this->_redirect('*/*');
    }

    public function massDisableAction()
    {
        $news = $this->getRequest()->getParam('news', null);
        if (is_array($news) && sizeof($news) > 0) {
            $counter = 0;
            try {
                $news_model = Mage::getModel('dsnews/news');
                foreach ($news as $id) {
                    $new = $news_model->load($id);
                    if ($new->getStatus()) {
                        $new->setStatus(0);
                        $new->save();
                        $counter++;
                    }
                }
                $this->_getSession()->addSuccess($this->__('Total of %d news have been Disable', $counter));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        } else {
            $this->_getSession()->addError($this->__('Please select news'));
        }
        $this->_redirect('*/*');
    }
}
