<?php

namespace App\Services;

use App\Repositories\CategoryRepository;

class CategoryService
{
    protected $repository;

    public function __construct(CategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createCategory(array $data)
    {
        // Log incoming data
        \Log::info('Register Category data received', $data);

        try {
            $category = $this->repository->create($data);

            // Log successful creation with category details
            \Log::info('Category successfully created', [
                'id' => $category->id,
                'category_name' => $category->category_name,
                'category_type' => $category->category_type,
            ]);

            return $category;
        } catch (\Exception $e) {
            // Log the error message
            \Log::error('Error registering category: ' . $e->getMessage());

            // Re-throw the exception to be caught in the controller
            throw $e;
        }
    }

    public function getAllCategories()
    {
        try {
            \Log::info('Getting all categories');
            $categories = $this->repository->all();
            return $categories;
        } catch (\Exception $e) {
            \Log::error('Error getting all categories: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getByCategoryName($categoryName, $merchantid)
    {
        try {
            \Log::info('Getting category by name: ' . $categoryName);
            $category = $this->repository->getByCategoryName($categoryName, $merchantid);
            if (!$category) {
                \Log::info('Category not found with name: ' . $categoryName);
                return response()->json(['message' => 'Category not found'], 404);
            }
            return $category;
        } catch (\Exception $e) {
            \Log::error('Error getting category by name: ' . $categoryName . ': ' . $e->getMessage());
            throw $e;
        }
    }

    public function getByCategoryType($categoryType, $merchantid)
    {
        try {
            \Log::info('Getting category by type: ' . $categoryType);
            $category = $this->repository->getByCategoryType($categoryType, $merchantid);
            if (!$category) {
                \Log::info('Category not found with type: ' . $categoryType);
                return response()->json(['message' => 'Category not found'], 404);
            }
            return $category;
        } catch (\Exception $e) {
            \Log::error('Error getting category by name: ' . $categoryType . ': ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateCategory($id, array $data)
    {
        try {
            \Log::info('Updating category ID: ' . $id . ' with data: ', $data);
            $category = $this->repository->findById($id);
            if (!$category) {
                \Log::info('Category not found with ID: ' . $id);
                return response()->json(['message' => 'Category not found'], 404);
            }
            $category->update($data);
            \Log::info('Category updated successfully: ', ['id' => $category->id]);
            return $category;
        } catch (\Exception $e) {
            \Log::error('Error updating category: ' . $id . ' with data: ' . $e->getMessage());
            throw $e;

        }
    }

}
