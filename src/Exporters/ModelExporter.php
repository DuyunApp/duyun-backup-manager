<?php

namespace DuyunApp\DuyunBackupManager\Exporters;

class ModelExporter
{
    protected string $modelClass;
    protected array $filters = [];
    protected array $columns = ['*'];
    protected array $with = [];

    public static function for(string $modelClass): self
    {
        $instance = new self;
        $instance->modelClass = $modelClass;
        return $instance;
    }

    public function filters(array $filters): self
    {
        $this->filters = $filters;
        return $this;
    }

    public function columns(array $columns): self
    {
        $this->columns = $columns;
        return $this;
    }

    public function with(array|string $with): self
    {
        if (is_array($with)) {
            $this->with = $with;
        } else {
            $this->with[] = $with;
        }
        return $this;
    }

  

    /**
     * جلب البيانات وربما حذفها
     */
    public function get()
    {
        $query = ($this->modelClass)::query();

        if (count($this->with) > 0) {
            $query->with($this->with);
        }

        foreach ($this->filters as $field => $condition) {
            if (is_array($condition)) {
                [$operator, $value] = $condition;
                $query->where($field, $operator, $value);
            } else {
                $query->where($field, $condition);
            }
        }

        $records = $query->get($this->columns);

        // 🧹 إذا تم تفعيل الحذف بعد الجلب
        return $records->toArray();
    }
}
