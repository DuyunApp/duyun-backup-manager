<?php

namespace DuyunApp\DuyunBackupManager\Exporters;

class ModelExporter
{
    protected string $modelClass;
    protected array $filters = [];
    protected array $columns = ['*'];
    protected array $with = [];
    protected array $attrs = [];


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
    public function attr(array|string $attr): self
    {
        if (is_array($attr)) {
            $this->attrs = $attr;
        } else {
            $this->attrs[] = $attr;
        }
        return $this;
    }


    private function dotMerge(array $data, array $attrs): array
    {
        foreach ($data as &$record) {
            foreach ($attrs as $attr => $label) {
                $value = $record[__($attr)] ?? data_get($record, $attr);

                if ($value === null || $value === '') {
                    continue;
                }

                $decoded = is_string($value) ? json_decode($value, true) : null;
                if (is_array($decoded)) {
                    $prefix = $label . '.';
                    foreach ($decoded as $k => $v) {
                        $record[__($prefix . $k)] = $v;
                    }
                } else {
                    $record[__($label)] = $value;
                }
            }
        }
        unset($record);

        return $data;
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

        $records = $query->get($this->columns)->toArray();

        if (count($this->attrs) > 0) {
            $records = $this->dotMerge($records, $this->attrs);
        }

        return $records;

    }
}
