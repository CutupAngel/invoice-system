<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;

use App\Order;
use App\User;
use App\Order_Group;
use App\Package;
use App\Package_Cycle;
use App\Package_File;
use App\Package_Options;
use App\Package_Option_Values;
use App\Options_To_Packages;
use App\PackageSetting;
use App\TaxClasses;
use Illuminate\Http\Request;
use Response;
use Permissions;
use Storage;
use Integrations;

class PackageController extends Controller
{
		public $user = null;
		public $api_type = null;

		public function __construct(Request $request)
		{
				$user = User::where('sandbox_api_key', $request->header('token'))
											->orWhere('live_api_key', $request->header('token'))
											->first();

				if(!$user)
        {
            return Response::json([
                                      'success' => false,
                                      'errors' => 'Invalid token.'
                                  ],
                                  401);
        }

				$this->user = $user;

				if($user->sandbox_api_key == $request->header('token')) $this->api_type = 'sandbox';
				else if($user->live_api_key == $request->header('token')) $this->api_type = 'live';
		}

		/* Create Group
			 Params
			 Header:
				- token: string (required)

				Body:
				- name: string (required)
				- description: string (optional)
				- type: integer (required)
		*/
		public function createGroup(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('name'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Name is required.';
				}
				if(!$request->has('type'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Type is required.';
				}
				if($request->type && !is_numeric($request->type))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Type format is invalid.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$name = $request->name;
				$description = '';
				if($request->description) $description = $request->description;
				$url = str_slug($name, '-');
				$type = $request->type;

				$orderGroup = new Order_Group();
				$orderGroup->user_id = $this->user->id;
				$orderGroup->name = $name;
				$orderGroup->description = $description;
				$orderGroup->url = $url;
				$orderGroup->type = $type;
				$orderGroup->api_type = $this->api_type;
				$orderGroup->save();

				return Response::json([
						'success' => true,
						'message' => 'Order Group created successfully',
						'id' => $orderGroup->id
				], 200);
		}

		/* Update Group
			 Params
			 Header:
				- token: string (required)

				Body:
				- id: integer (required)
				- name: string (required)
				- description: string (optional)
				- type: integer (required)
		*/
		public function updateGroup(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'ID is required.';
				}
				if(!$request->has('name'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Name is required.';
				}
				if(!$request->has('type'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Type is required.';
				}
				if($request->type && is_int($request->type))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Type format is invalid.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$id = $request->id;
				$name = $request->name;
				$description = '';
				if($request->description) $description = $request->description;
				$url = str_slug($name, '-');
				$type = $request->type;

				if($this->api_type == 'sandbox')
					$orderGroup = Order_Group::where('id', $id)
																			->where('api_type', $this->api_type)
																			->first();
				else
					$orderGroup = Order_Group::where('id', $id)
																			->where(function($query) {
																					$query->whereNull('api_type')
																								->orWhere('api_type', $this->api_type);
																			})
																			->first();

				if(!$orderGroup)
				{
					return Response::json([
																		'success' => false,
																		'errors' => 'Group ID: ' . $id . ' not found.'
																],
																400);
				}

				$orderGroup->user_id = $this->user->id;
				$orderGroup->name = $name;
				$orderGroup->description = $description;
				$orderGroup->url = $url;
				$orderGroup->type = $type;
				$orderGroup->api_type = $this->api_type;
				$orderGroup->save();

				return Response::json([
						'success' => true,
						'message' => 'Order Group updated successfully',
						'id' => $orderGroup->id
				], 200);
		}

		/* Lists Group
			 Params
			 Header:
			 	- token: string (required)
		*/
		public function listsGroup(Request $request)
		{
				if($this->api_type == 'sandbox')
					$orderGroups = Order_Group::where('api_type', $this->api_type)->orderBy('id')->get();
				else
					$orderGroups = Order_Group::where(function($query) {
																					$query->whereNull('api_type')
																								->orWhere('api_type', $this->api_type);
																			})
																			->orderBy('id')
																			->get();

				return Response::json([
						'success' => true,
						'groups' => $orderGroups
				], 200);
		}

		/* Get Group
			 Params
			 Header:
			 	- token: string (required)

			Body:
				- id: integer (required)
		*/
		public function getGroup(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$id = $request->id;

				if($this->api_type == 'sandbox')
					$orderGroup = Order_Group::where('id', $id)
																			->where('api_type', $this->api_type)
																			->first();
				else
					$orderGroup = Order_Group::where('id', $id)
																			->where(function($query) {
																					$query->whereNull('api_type')
																								->orWhere('api_type', $this->api_type);
																			})
																			->first();

				if(!$orderGroup)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Group ID: ' . $id . ' not found.'
																	],
																	401);
				}

				return Response::json([
						'success' => true,
						'group' => $orderGroup
				], 200);
		}

		/* Delete Group
			 Params
			 Header:
				- token: string (required)

				Body:
				- id: integer (required)
		*/
		public function deleteGroup(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$id = $request->id;

				if($this->api_type == 'sandbox')
					$orderGroup = Order_Group::where('id', $id)
																			->where('api_type', $this->api_type)
																			->first();
				else
					$orderGroup = Order_Group::where('id', $id)
																			->where(function($query) {
													                $query->whereNull('api_type')
													                      ->orWhere('api_type', $this->api_type);
													            })
																			->first();

				if(!$orderGroup)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Group ID: ' . $id . ' not found.'
																	],
																	401);
				}

				$orderGroup->delete();

				return Response::json([
						'success' => true,
						'message' => 'Order Group: ' . $id . ' deleted successfully',
				], 200);
		}

		/* Create Package
			 Params
			 Header:
				- token: string (required)

				Body:
				- group_id: integer (required)
				- name: string (required)
				- description: string (optional)
				- tax: integer (optional)
				- url: string (optional)
				- prorate: enum ('Y', 'N') (optional)
				- trial: integer (optional)
				- options[]: integer (optional)
				- keep[]: integer (optional)
				- files[]: file upload (optional)
				- product_images[]: file upload (optional)
				- cycle[cycle][]: integer (optional)
				- cycle[id][]: integer (optional)
				- cycle[price][]: double (optional)
				- cycle[setup][]: double (optional)
				- featured: enum ('0', '1') (optional)
		*/
		public function createPackage(Request $request)
		{
				$errorMessage = '';

				if(!$request->has('group_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Group ID is required.';
				}

				if(!$request->has('name'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Name is required.';
				}

				if($request->tax && !is_numeric($request->tax))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Tax format is invalid.';
				}

				if($request->prorate)
				{
						if($request->prorate == 'Y' || $request->prorate == 'N')
						{
								//correct format
						}
						else
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Prorate format is invalid.';
						}
				}

				if($request->trial && !is_numeric($request->trial))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Trial format is invalid.';
				}

				if($request->has('options'))
				{
						if(!is_array($request->post('options')))
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Options format is invalid.';
						}
						else
						{
								foreach($request->options as $k=>$v)
								{
									$option = Package_Options::find($v);
									if(!$option)
									{
											if($errorMessage != '') $errorMessage .= ', ';
											$errorMessage .= 'Options: ' . $v . ' not found.';
									}
								}
						}
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$groupId = $request->group_id;

				try {
            $group = Order_Group::findOrFail($groupId);
            $group->permissionCheck($this->user->id);
        } catch (\Exception $e) {
						return Response::json([
																			'success' => false,
																			'errors' => 'Unable to create a package for the selected group.'
																	],
																	401);
        }

				$name = $request->name;
				$description = '';
				if($request->description) $description = $request->description;
				$tax = 0;
				if($request->tax) $tax = $request->tax;
				$prorate = $request->prorate == 'Y';
				$url = '';
				if($request->url) $tax = urlencode($request->url);
				$trial = 0;
				if($request->trial) $trial = $request->trial;
				$featured = 0;
				if($request->featured) $featured = $request->featured == '1';

				$package = new Package();
				$package->group_id = $group->id;
				$package->name = $name;
        $package->description = $description;
        $package->tax = $tax;
        $package->prorate = $prorate;
				$package->api_type = $this->api_type;
        $package->url = $url;
        $package->trial = $trial;
        $package->is_featured = $featured;
				$package->save();

				if (!$request->has('keep')) {
            foreach ($package->files as $file) {
                $file->delete();
            }
        } else {
            $fileIds = [];
            foreach ($request->keep as $id) {
                $fileIds[] = $id;
            }

            $files = Package_File::where('package_id', $package->id)->whereNotIn('id', $fileIds)->get();
            foreach ($files as $file) {
                $file->delete();
            }
        }

				if ($request->hasFile('files')) {
            $currentSpace = $this->user->getSpace();

            foreach ($request->file('files') as $file) {
                if (!$file->isValid()) {
                    $errors[] = "File: {$file->getClientOriginalName()} failed to upload. Reason: {$file->getErrorMessage()}";
                    continue;
                }

                if ($currentSpace['free'] - $file->getSize() < 0) {
                    $errors[] = "File: {$file->getClientOriginalName()} failed to upload. Reason: Not enough space to store file.";
                    continue;
                }

                $filepath = "clientProductFiles/" . $this->user->id . '/' . $file->getClientOriginalName();

                Storage::disk('minio')->put($filepath, file_get_contents($file->getRealPath()));

                if (Storage::disk('minio')->has($filepath)) {
                    $dbFile = new Package_File();
                    $dbFile->package_id = $package->id;
                    $dbFile->filename = $file->getClientOriginalName();
                    $dbFile->path = $filepath;
                    $dbFile->size = $file->getSize();
                    $dbFile->mime = $file->getMimeType();
                    $dbFile->save();
                } else {
                    $errors[] = "File: {$file->getClientOriginalName()} failed to upload.";
                }
            }
        }

				$cycles = $package->cycles;
        $savedCycles = [];
				if($request->post('cycle')['id'])
				{
		        foreach ($request->post('cycle')['id'] as $i => $id)
						{
								$cycle = new Package_Cycle();
		            $cycle->package_id = $package->id;
								$cycle->price = 0.00;
								if($request->post('cycle')['price'][$i] && is_double($request->post('cycle')['price'][$i]))
		            	$cycle->price = $request->post('cycle')['price'][$i];

								$cycle->fee = 0.00;
								if($request->post('cycle')['setup'][$i] && is_double($request->post('cycle')['setup'][$i]))
		            	$cycle->fee = $request->post('cycle')['setup'][$i];

								$cycle->cycle = 0;
								if($request->post('cycle')['cycle'][$i] && is_int($request->post('cycle')['cycle'][$i]))
		            	$cycle->cycle = $request->post('cycle')['cycle'][$i];

		            $cycle->save();

		            $savedCycles[] = $cycle->id;
		        }
				}

				foreach ($cycles as $cycle) {
            if (in_array($cycle->id, $savedCycles)) {
                continue;
            }

            $cycle->delete();
        }

				if($request->has('options'))
				{
					Options_To_Packages::where('package_id','=',$package->id)->delete();
					foreach($request->options as $k=>$v)
					{
						$link = new Options_To_Packages();
						$link->package_id = $package->id;
						$link->option_id = $v;
						$link->save();
					}
				}
		    else
		    {
		        Options_To_Packages::where('package_id','=',$package->id)->delete();
		    }

				$package->domainIntegration = false;

				if($request->hasfile('product_images'))
        {
          foreach($request->file('product_images') as $image)
          {
							$filepath = "clientProductFiles/" . Auth::User()->id . '/' . $image->getClientOriginalName();
							Storage::disk('minio')->put($filepath, file_get_contents($image->getRealPath()));

              $packageImage = new Package_Image();
              $packageImage->package_id = $package->id;
							$packageImage->filename = $image->getClientOriginalName();
              $packageImage->path = $filepath;
              $packageImage->save();
          }
        }

				$package->save();

				return Response::json([
						'success' => true,
						'message' => 'Package created successfully',
						'id' => $package->id
				], 200);

		}

		/* Update Package
			 Params
			 Header:
				- token: string (required)

				Body:
				- id: integer (required)
				- group_id: integer (required)
				- name: string (required)
				- description: string (optional)
				- tax: integer (optional)
				- url: string (optional)
				- prorate: enum ('Y', 'N') (optional)
				- trial: integer (optional)
				- options[]: integer (optional)
				- keep[]: integer (optional)
				- files[]: file upload (optional)
				- product_images[]: file upload (optional)
				- cycle[cycle][]: integer (optional)
				- cycle[id][]: integer (optional)
				- cycle[price][]: double (optional)
				- cycle[setup][]: double (optional)
				- featured: enum ('0', '1') (optional)
		*/
		public function updatePackage(Request $request)
		{
				$errorMessage = '';

				if(!$request->has('id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'ID is required.';
				}

				if(!$request->has('group_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Group ID is required.';
				}

				if(!$request->has('name'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Name is required.';
				}

				if($request->tax && !is_numeric($request->tax))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Tax format is invalid.';
				}

				if($request->prorate)
				{
						if($request->prorate == 'Y' || $request->prorate == 'N')
						{
								//correct format
						}
						else
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Prorate format is invalid.';
						}
				}

				if($request->trial && !is_numeric($request->trial))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Trial format is invalid.';
				}

				if($request->has('options'))
				{
						if(!is_array($request->post('options')))
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Options format is invalid.';
						}
						else
						{
								foreach($request->options as $k=>$v)
								{
									$option = Package_Options::find($v);
									if(!$option)
									{
											if($errorMessage != '') $errorMessage .= ', ';
											$errorMessage .= 'Options: ' . $v . ' not found.';
									}
								}
						}
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$groupId = $request->group_id;

				try {
						$group = Order_Group::findOrFail($groupId);
						$group->permissionCheck($this->user->id);
				} catch (\Exception $e) {
						return Response::json([
																			'success' => false,
																			'errors' => 'Unable to create a package for the selected group.'
																	],
																	401);
				}

				$id = $request->id;
				$name = $request->name;
				$description = '';
				if($request->description) $description = $request->description;
				$tax = 0;
				if($request->tax) $tax = $request->tax;
				$prorate = $request->prorate == 'Y';
				$url = '';
				if($request->url) $tax = urlencode($request->url);
				$trial = 0;
				if($request->trial) $trial = $request->trial;
				$featured = 0;
				if($request->featured) $featured = $request->featured == '1';

				$package = Package::find($id);

				if(!$package)
				{
					return Response::json([
																		'success' => false,
																		'errors' => 'Package not found.'
																],
																400);
				}
				$package->group_id = $group->id;
				$package->name = $name;
				$package->description = $description;
				$package->tax = $tax;
				$package->prorate = $prorate;
				$package->api_type = $this->api_type;
				$package->url = url('order/' . $package->group_id  . '/' . $package->id); //$url;
				$package->trial = $trial;
        $package->is_featured = $featured;
				$package->save();

				if (!$request->has('keep')) {
						foreach ($package->files as $file) {
								$file->delete();
						}
				} else {
						$fileIds = [];
						foreach ($request->keep as $id) {
								$fileIds[] = $id;
						}

						$files = Package_File::where('package_id', $package->id)->whereNotIn('id', $fileIds)->get();
						foreach ($files as $file) {
								$file->delete();
						}
				}

				if ($request->hasFile('files')) {
						$currentSpace = $this->user->getSpace();

						foreach ($request->file('files') as $file) {
								if (!$file->isValid()) {
										$errors[] = "File: {$file->getClientOriginalName()} failed to upload. Reason: {$file->getErrorMessage()}";
										continue;
								}

								if ($currentSpace['free'] - $file->getSize() < 0) {
										$errors[] = "File: {$file->getClientOriginalName()} failed to upload. Reason: Not enough space to store file.";
										continue;
								}

								$filepath = "clientProductFiles/" . $this->user->id . '/' . $file->getClientOriginalName();

								Storage::disk('minio')->put($filepath, file_get_contents($file->getRealPath()));

								if (Storage::disk('minio')->has($filepath)) {
										$dbFile = new Package_File();
										$dbFile->package_id = $package->id;
										$dbFile->filename = $file->getClientOriginalName();
										$dbFile->path = $filepath;
										$dbFile->size = $file->getSize();
										$dbFile->mime = $file->getMimeType();
										$dbFile->save();
								} else {
										$errors[] = "File: {$file->getClientOriginalName()} failed to upload.";
								}
						}
				}

				$cycles = $package->cycles;
				$savedCycles = [];
				if($request->post('cycle')['id'])
				{
						foreach ($request->post('cycle')['id'] as $i => $id)
						{
								if ($cycles->contains('id', $id)) {
										$cycle = Package_Cycle::findOrFail($id);
								} else {
										continue;
								}
								$cycle->package_id = $package->id;
								$cycle->price = 0.00;
								if($request->post('cycle')['price'][$i] && is_double($request->post('cycle')['price'][$i]))
									$cycle->price = $request->post('cycle')['price'][$i];

								$cycle->fee = 0.00;
								if($request->post('cycle')['setup'][$i] && is_double($request->post('cycle')['setup'][$i]))
									$cycle->fee = $request->post('cycle')['setup'][$i];

								$cycle->cycle = 0;
								if($request->post('cycle')['cycle'][$i] && is_int($request->post('cycle')['cycle'][$i]))
									$cycle->cycle = $request->post('cycle')['cycle'][$i];

								$cycle->save();

								$savedCycles[] = $cycle->id;
						}
				}

				foreach ($cycles as $cycle) {
						if (in_array($cycle->id, $savedCycles)) {
								continue;
						}

						$cycle->delete();
				}

				if($request->has('options'))
				{
					Options_To_Packages::where('package_id','=',$package->id)->delete();
					foreach($request->options as $k=>$v)
					{
						$link = new Options_To_Packages();
						$link->package_id = $package->id;
						$link->option_id = $v;
						$link->save();
					}
				}
				else
				{
						Options_To_Packages::where('package_id','=',$package->id)->delete();
				}

				$package->domainIntegration = false;

				if($request->hasfile('product_images'))
        {
          foreach($request->file('product_images') as $image)
          {
							$filepath = "clientProductFiles/" . Auth::User()->id . '/' . $image->getClientOriginalName();
							Storage::disk('minio')->put($filepath, file_get_contents($image->getRealPath()));

              $packageImage = new Package_Image();
              $packageImage->package_id = $package->id;
              $packageImage->filename = $image->getClientOriginalName();
              $packageImage->path = $filepath;
              $packageImage->save();
          }
        }

				$package->save();

				return Response::json([
						'success' => true,
						'message' => 'Package updated successfully',
						'id' => $package->id
				], 200);
		}

		/* Show Package
			 Params
			 Header:
				- token: string (required)

				Body:
				- id: integer (required)
		*/
		public function showPackage(Request $request)
		{
				$errorMessage = '';

				if(!$request->has('id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$id = $request->id;
				$package = Package::where('id', $id)
														->where('exclude_from_api', '0')
														->first();

				if(!$package)
				{
					return Response::json([
																		'success' => false,
																		'errors' => 'Package not found.'
																],
																400);
				}

				$packageObj = new \stdClass();
				$packageObj->id = $package->id;
				$packageObj->group = $package->group;
				$packageObj->name = $package->name;
				$packageObj->description = $package->description;
				$packageObj->prorate = $package->trial;
				$packageObj->theme = $package->theme;
				$packageObj->type = $package->type;
				$packageObj->api_type = $package->api_type;
				$packageObj->url = url('order/' . $package->group_id  . '/' . $package->id); //$package->url;
				$packageObj->integration = $package->integration;
				$packageObj->created_at = $package->created_at;
				$packageObj->updated_at = $package->updated_at;
				$packageObj->deleted_at = $package->deleted_at;
				$packageObj->domainIntegration = $package->domainIntegration;
				$packageObj->featured = $package->is_featured;
				$packageObj->link = $package->link;
				$packageObj->options = $package->options;

				$cycleArr = [];
				foreach($package->cycles as $cycle)
				{
						$cycleObj = new \stdClass();
						$cycleObj->id = $cycle->id;
						$cycleObj->package_id = $cycle->package_id;
						$cycleObj->price = $cycle->price;
						$cycleObj->fee = $cycle->fee;
						$cycleObj->cycle = $cycle->cycle;
						$cycleObj->cycle_text = $cycle->cycle();
						$cycleObj->created_at = $cycle->created_at;
						$cycleObj->updated_at = $cycle->updated_at;
						$cycleObj->deleted_at = $cycle->deleted_at;
						$cycleArr[] = $cycleObj;
				}

				$packageObj->cycles = $cycleArr;
				$packageObj->files = $package->files;

				$imagesArr = [];
				foreach($package->images as $image)
				{
						$imageObj = new \stdClass();
						$imageObj->id = $image->id;
						$imageObj->package_id = $image->package_id;
						$imageObj->filename = $image->filename;
						$imageObj->path = $image->path;
						$imageObj->url = config('app.CDN') . $image->path;
						$imageObj->created_at = $image->created_at;
						$imageObj->updated_at = $image->updated_at;
						$imageObj->deleted_at = $image->deleted_at;
						$imagesArr[] = $imageObj;
				}

				$packageObj->images = $imagesArr;

				$packageObj->settings = $package->settings;

				return Response::json([
						'success' => true,
						'package' => $packageObj
				], 200);
		}

		/* Lists Package
			 Params
			 Header:
			 	- token: string (required)

			 Body:
				- featured: enum (0 / 1) (optional)
		*/
		public function listsPackage(Request $request)
		{
				if($this->api_type == 'sandbox')
				{
					$packages = Package::where('api_type', $this->api_type);
				}
				else
				{
					$packages = Package::where(function($query) {
										                $query->whereNull('api_type')
										                      ->orWhere('api_type', $this->api_type);
										            });
				}

				if($request->has('featured'))
				{
					 $packages = $packages->where('is_featured', $request->featured);
				}

				$packages = $packages->where('exclude_from_api', '0')->orderBy('id')->get();

				$packageArr = [];
				foreach($packages as $package)
				{
						$packageObj = new \stdClass();
						$packageObj->id = $package->id;
						$packageObj->group_id = $package->group_id;
						$packageObj->name = $package->name;
						$packageObj->description = $package->description;
						$packageObj->tax = $package->tax;
						$packageObj->prorate = $package->prorate;
						$packageObj->trial = $package->trial;
						$packageObj->theme = $package->theme;
						$packageObj->type = $package->type;
						$packageObj->api_type = $package->api_type;
						$packageObj->url = url('order/' . $package->group_id  . '/' . $package->id); //$package->url;
						$packageObj->integration = $package->integration;
						$packageObj->featured = $package->is_featured;
						$packageObj->created_at = $package->created_at;
						$packageObj->updated_at = $package->updated_at;
						$packageObj->deleted_at = $package->deleted_at;
						$packageObj->domainIntegration = $package->domainIntegration;
						$packageObj->cycle = $package->cycles;
						$packageObj->options = $package->options;
						$packageObj->files = $package->files;

						$imagesArr = [];
						foreach($package->images as $image)
						{
								$imageObj = new \stdClass();
								$imageObj->id = $image->id;
								$imageObj->package_id = $image->package_id;
								$imageObj->filename = $image->filename;
								$imageObj->path = $image->path;
								$imageObj->url = config('app.CDN') . $image->path;
								$imageObj->created_at = $image->created_at;
								$imageObj->updated_at = $image->updated_at;
								$imageObj->deleted_at = $image->deleted_at;
								$imagesArr[] = $imageObj;
						}

						$packageObj->images = $imagesArr;

						$packageArr[] = $packageObj;
				}

				return Response::json([
						'success' => true,
						'packages' => $packageArr
				], 200);
		}

		/* Delete Package
			 Params
			 Header:
			 	- token: string (required)

			 Body:
				-	id: integer (required)
		*/
		public function DeletePackage(Request $request)
		{
				$errorMessage = '';

				if(!$request->has('id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$id = $request->id;
				if($this->api_type == 'sandbox')
				{
					$package = Package::where('id', $id)
																->where('api_type', $this->api_type);
				}
				else
				{
					$package = Package::where('id', $id)
																->where(function($query) {
										                $query->whereNull('api_type')
										                      ->orWhere('api_type', $this->api_type);
										            });
				}

				$package = $package->where('exclude_from_api', '0')->first();

				if(!$package)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Package ID: ' . $id . ' not found.'
																	],
																	401);
				}

				$package->delete();

				return Response::json([
						'success' => true,
						'message' => 'Package: ' . $id . ' deleted successfully',
				], 200);

		}

		/* Get Package
			 Params
			 Header:
			 	- token: string (required)

			 Body:
				-	id: integer (required)
		*/
		public function GetPackage(Request $request)
		{
				$errorMessage = '';

				if(!$request->has('id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$id = $request->id;
				if($this->api_type == 'sandbox')
				{
					$package = Package::where('id', $id)
																->where('api_type', $this->api_type);
				}
				else
				{
					$package = Package::where('id', $id)
																->where(function($query) {
										                $query->whereNull('api_type')
										                      ->orWhere('api_type', $this->api_type);
										            });
				}

				$package = $package->where('exclude_from_api', '0')->first();

				if(!$package)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Package ID: ' . $id . ' not found.'
																	],
																	401);
				}

				$packageObj = new \stdClass();
				$packageObj->id = $package->id;
				$packageObj->group_id = $package->group_id;
				$packageObj->name = $package->name;
				$packageObj->description = $package->description;
				$packageObj->tax = $package->tax;
				$packageObj->prorate = $package->prorate;
				$packageObj->trial = $package->trial;
				$packageObj->theme = $package->theme;
				$packageObj->type = $package->type;
				$packageObj->api_type = $package->api_type;
				$packageObj->url = url('order/' . $package->group_id  . '/' . $package->id); //$package->url;
				$packageObj->integration = $package->integration;
				$packageObj->created_at = $package->created_at;
				$packageObj->updated_at = $package->updated_at;
				$packageObj->deleted_at = $package->deleted_at;
				$packageObj->domainIntegration = $package->domainIntegration;
				$packageObj->cycle = $package->cycles;
				$packageObj->options = $package->options;
				$packageObj->files = $package->files;

				return Response::json([
						'success' => true,
						'package' => $packageObj,
				], 200);

		}

		/* Create Package Option
			 Params
			 Header:
				- token: string (required)

				Body:
				- internal_name: string (required)
				- display_name: string (required)
				- field_type: integer (0 - 5) (required)
				- required: enum ('Y', 'N') (optional)
		*/
		public function createPackageOption(Request $request)
		{
				$errorMessage = '';

				if(!$request->has('internal_name'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Internal Name is required.';
				}

				if(!$request->has('display_name'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Display Name is required.';
				}

				if(!$request->has('field_type'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Field Type is required.';
				}

				if($request->field_type)
				{
						if($request->field_type && !is_numeric($request->field_type))
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Field Type format is invalid.';
						}
						else if($request->field_type && intVal($request->field_type) < 0 || intVal($request->field_type) > 5)
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Field Type value is invalid.';
						}
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$internal_name = $request->internal_name;
				$display_name = $request->display_name;
				$type = $request->field_type;
				$required = $request->required == 'Y';

				$option = new Package_Options();
				$option->user_id = $this->user->id;
				$option->internal_name = $internal_name;
				$option->display_name = $display_name;
				$option->type = $type;
				$option->required = $required;
				$option->api_type = $this->api_type;
				$option->save();

				return Response::json([
						'success' => true,
						'message' => 'Package Option created successfully',
						'id' => $option->id
				], 200);
		}

		/* Update Package Option
			 Params
			 Header:
				- token: string (required)

				Body:
				- id: integer (required)
				- internal_name: string (required)
				- display_name: string (required)
				- field_type: integer (0 - 5) (required)
				- required: enum ('Y', 'N') (optional)
		*/
		public function updatePackageOption(Request $request)
		{
				$errorMessage = '';

				if(!$request->has('id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'ID is required.';
				}

				if(!$request->has('internal_name'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Internal Name is required.';
				}

				if(!$request->has('display_name'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Display Name is required.';
				}

				if(!$request->has('field_type'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Field Type is required.';
				}

				if($request->field_type)
				{
						if($request->field_type && !is_numeric($request->field_type))
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Field Type format is invalid.';
						}
						else if($request->field_type && intVal($request->field_type) < 0 || intVal($request->field_type) > 5)
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Field Type value is invalid.';
						}
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$internal_name = $request->internal_name;
				$display_name = $request->display_name;
				$type = $request->field_type;
				$required = $request->required == 'Y';

				$id = $request->id;
				if($this->api_type == 'sandbox')
					$option = Package_Options::where('id', $id)
																->where('api_type', $this->api_type)
																->first();
				else
					$option = Package_Options::where('id', $id)
																->where(function($query) {
										                $query->whereNull('api_type')
										                      ->orWhere('api_type', $this->api_type);
										            })
																->first();

				if(!$option)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Package Option ID: ' . $id . ' not found.'
																	],
																	401);
				}

				$option->user_id = $this->user->id;
				$option->internal_name = $internal_name;
				$option->display_name = $display_name;
				$option->type = $type;
				$option->required = $required;
				$option->api_type = $this->api_type;
				$option->save();

				return Response::json([
						'success' => true,
						'message' => 'Package Option updated successfully',
						'id' => $option->id
				], 200);
		}

		/* Lists Package Option
			 Params
			 Header:
			 	- token: string (required)
		*/
		public function listsPackageOption(Request $request)
		{
				if($this->api_type == 'sandbox')
					$options = Package_Options::where('api_type', $this->api_type)->orderBy('id')->get();
				else
					$options = Package_Options::whereNull('api_type')
																			->orWhere('api_type', $this->api_type)
																			->orderBy('id')
																			->get();

				return Response::json([
						'success' => true,
						'package_options' => $options
				], 200);
		}

		/* Get Package Option
			 Params
			 Header:
			 	- token: string (required)

			 Body:
				-	id: integer (required)
		*/
		public function getPackageOption(Request $request)
		{
				$errorMessage = '';

				if(!$request->has('id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$id = $request->id;
				if($this->api_type == 'sandbox')
					$option = Package_Options::where('id', $id)
																->where('api_type', $this->api_type)
																->first();
				else
					$option = Package_Options::where('id', $id)
																->where(function($query) {
																		$query->whereNull('api_type')
																					->orWhere('api_type', $this->api_type);
																})
																->first();

					if(!$option)
					{
							return Response::json([
																				'success' => false,
																				'errors' => 'Package Option ID: ' . $id . ' not found.'
																		],
																		401);
					}

					return Response::json([
							'success' => true,
							'package_option' => $option
					], 200);
		}

		/* Delete Package Option
			 Params
			 Header:
			 	- token: string (required)

			 Body:
				-	id: integer (required)
		*/
		public function DeletePackageOption(Request $request)
		{
				$errorMessage = '';

				if(!$request->has('id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$id = $request->id;
				if($this->api_type == 'sandbox')
					$option = Package_Options::where('id', $id)
																->where('api_type', $this->api_type)
																->first();
				else
					$option = Package_Options::where('id', $id)
																->where(function($query) {
										                $query->whereNull('api_type')
										                      ->orWhere('api_type', $this->api_type);
										            })
																->first();

					if(!$option)
					{
							return Response::json([
																				'success' => false,
																				'errors' => 'Package Option ID: ' . $id . ' not found.'
																		],
																		401);
					}

					$option->delete();

					return Response::json([
							'success' => true,
							'message' => 'Package Option: ' . $id . ' deleted successfully',
					], 200);

		}

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
		public function getPackageByCustomer(Request $request)
        {
            $this->validate($request, ['customer_id' => 'required|integer']);

            $packages = Order::where('customer_id', $request->get('customer_id'))
                ->get()
                ->map(function ($order) {
                    return $order->package;
                })
                ->all();

            $packageArr = [];
            foreach($packages as $package) {
                $packageObj = new \stdClass();
                $packageObj->id = $package->id;
                $packageObj->group_id = $package->group_id;
                $packageObj->name = $package->name;
                $packageObj->description = $package->description;
                $packageObj->tax = $package->tax;
                $packageObj->prorate = $package->prorate;
                $packageObj->trial = $package->trial;
                $packageObj->theme = $package->theme;
                $packageObj->type = $package->type;
                $packageObj->api_type = $package->api_type;
                $packageObj->url = url('order/' . $package->group_id  . '/' . $package->id); //$package->url;
                $packageObj->integration = $package->integration;
                $packageObj->created_at = $package->created_at;
                $packageObj->updated_at = $package->updated_at;
                $packageObj->deleted_at = $package->deleted_at;
                $packageObj->domainIntegration = $package->domainIntegration;
                $packageObj->cycle = $package->cycles;
                $packageObj->options = $package->options;
                $packageObj->files = $package->files;

                $packageArr[] = $packageObj;
            }

            return response()->json([
                'success' => true,
                'packages' => $packageArr
            ]);
        }
}
