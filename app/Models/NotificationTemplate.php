<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'type',
        'name',
        'description',
        'channel',
        'subject',
        'template',
        'title_template',
        'message_template',
        'available_variables',
        'action_url_template',
        'action_text',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'available_variables' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope by channel
     */
    public function scopeChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Replace variables in template
     */
    public function replaceVariables(array $data): array
    {
        $result = [];

        // Replace subject
        if ($this->subject) {
            $result['subject'] = $this->replacePlaceholders($this->subject, $data);
        }

        // Replace title
        if ($this->title_template) {
            $result['title'] = $this->replacePlaceholders($this->title_template, $data);
        }

        // Replace message
        if ($this->message_template) {
            $result['message'] = $this->replacePlaceholders($this->message_template, $data);
        }

        // Replace action URL
        if ($this->action_url_template) {
            $result['action_url'] = $this->replacePlaceholders($this->action_url_template, $data);
        }

        $result['action_text'] = $this->action_text;

        return $result;
    }

    /**
     * Replace placeholders in string
     */
    private function replacePlaceholders(string $template, array $data): string
    {
        $result = $template;

        foreach ($data as $key => $value) {
            $placeholder = '{' . $key . '}';
            $result = str_replace($placeholder, $value, $result);
        }

        return $result;
    }

    /**
     * Get template by type
     */
    public static function getByType(string $type): ?self
    {
        return self::where('type', $type)
            ->where('is_active', true)
            ->first();
    }
}
