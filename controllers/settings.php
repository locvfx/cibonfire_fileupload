<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class settings extends Admin_Controller {

	//--------------------------------------------------------------------


	public function __construct()
	{
		parent::__construct();

		$this->auth->restrict('Fileupload.Settings.View');
		$this->load->model('fileupload_model', null, true);
		$this->lang->load('fileupload');
		
		Template::set_block('sub_nav', 'settings/_sub_nav');
	}

	//--------------------------------------------------------------------



	/*
		Method: index()

		Displays a list of form data.
	*/
	public function index()
	{

		// Deleting anything?
		if ($this->input->post('delete'))
		{
			$checked = $this->input->post('checked');

			if (is_array($checked) && count($checked))
			{
				$result = FALSE;
				foreach ($checked as $pid)
				{
					$result = $this->fileupload_model->delete($pid);
				}

				if ($result)
				{
					Template::set_message(count($checked) .' '. lang('fileupload_delete_success'), 'success');
				}
				else
				{
					Template::set_message(lang('fileupload_delete_failure') . $this->fileupload_model->error, 'error');
				}
			}
		}

		$records = $this->fileupload_model->find_all();

		Template::set('records', $records);
		Template::set('toolbar_title', 'Manage fileupload');
		Template::render();
	}

	//--------------------------------------------------------------------



	/*
		Method: create()

		Creates a fileupload object.
	*/
	public function create()
	{
		$this->auth->restrict('Fileupload.Settings.Create');

		if ($this->input->post('save'))
		{
			if ($insert_id = $this->save_fileupload())
			{
				// Log the activity
				$this->activity_model->log_activity($this->current_user->id, lang('fileupload_act_create_record').': ' . $insert_id . ' : ' . $this->input->ip_address(), 'fileupload');

				Template::set_message(lang('fileupload_create_success'), 'success');
				redirect(SITE_AREA .'/settings/fileupload');
			}
			else
			{
				Template::set_message(lang('fileupload_create_failure') . $this->fileupload_model->error, 'error');
			}
		}
		Assets::add_module_js('fileupload', 'fileupload.js');

		Template::set('toolbar_title', lang('fileupload_create') . ' fileupload');
		Template::render();
	}

	//--------------------------------------------------------------------



	/*
		Method: edit()

		Allows editing of fileupload data.
	*/
	public function edit()
	{
		$id = $this->uri->segment(5);

		if (empty($id))
		{
			Template::set_message(lang('fileupload_invalid_id'), 'error');
			redirect(SITE_AREA .'/settings/fileupload');
		}

		if ($this->input->post('save'))
		{
			$this->auth->restrict('Fileupload.Settings.Edit');

			if ($this->save_fileupload('update', $id))
			{
				// Log the activity
				$this->activity_model->log_activity($this->current_user->id, lang('fileupload_act_edit_record').': ' . $id . ' : ' . $this->input->ip_address(), 'fileupload');

				Template::set_message(lang('fileupload_edit_success'), 'success');
			}
			else
			{
				Template::set_message(lang('fileupload_edit_failure') . $this->fileupload_model->error, 'error');
			}
		}
		else if ($this->input->post('delete'))
		{
			$this->auth->restrict('Fileupload.Settings.Delete');

			if ($this->fileupload_model->delete($id))
			{
				// Log the activity
				$this->activity_model->log_activity($this->current_user->id, lang('fileupload_act_delete_record').': ' . $id . ' : ' . $this->input->ip_address(), 'fileupload');

				Template::set_message(lang('fileupload_delete_success'), 'success');

				redirect(SITE_AREA .'/settings/fileupload');
			} else
			{
				Template::set_message(lang('fileupload_delete_failure') . $this->fileupload_model->error, 'error');
			}
		}
		Template::set('fileupload', $this->fileupload_model->find($id));
		Assets::add_module_js('fileupload', 'fileupload.js');

		Template::set('toolbar_title', lang('fileupload_edit') . ' fileupload');
		Template::render();
	}

	//--------------------------------------------------------------------


	//--------------------------------------------------------------------
	// !PRIVATE METHODS
	//--------------------------------------------------------------------

	/*
		Method: save_fileupload()

		Does the actual validation and saving of form data.

		Parameters:
			$type	- Either "insert" or "update"
			$id		- The ID of the record to update. Not needed for inserts.

		Returns:
			An INT id for successful inserts. If updating, returns TRUE on success.
			Otherwise, returns FALSE.
	*/
	private function save_fileupload($type='insert', $id=0)
	{
		if ($type == 'update') {
			$_POST['id'] = $id;
		}

		

		if ($this->form_validation->run() === FALSE)
		{
			return FALSE;
		}

		// make sure we only pass in the fields we want
		
		$data = array();

		if ($type == 'insert')
		{
			$id = $this->fileupload_model->insert($data);

			if (is_numeric($id))
			{
				$return = $id;
			} else
			{
				$return = FALSE;
			}
		}
		else if ($type == 'update')
		{
			$return = $this->fileupload_model->update($id, $data);
		}

		return $return;
	}

	//--------------------------------------------------------------------



}