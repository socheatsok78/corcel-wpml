<?php

/**
 *  Post Model
 *
 * @author Socheat <https://github.com/socheatsok78>
 */

namespace Wpml;

use Corcel\Post as Corcel;
use Wpml\Translation\Translation;

class Post extends Corcel
{

  /**
   * The accessors to append to the model's array form.
   *
   * @var array
   */
  protected $appends = [
    'title',
    'slug',
    'content',
    'type',
    'mime_type',
    'url',
    'author_id',
    'parent_id',
    'created_at',
    'updated_at',
    'excerpt',
    'status',
    'image',

    // Translations
    'language',

    // Terms inside all taxonomies
    'terms',

    // Terms analysis
    'main_category',
    'keywords',
    'keywords_str',
    ];

    /**
     * Gets the value.
     * Tries to unserialize the object and returns the value if that doesn't work.
     *
     * @return value
     */
    public function getLanguageAttribute()
    {
      return $this->wpml->language_code;
    }


    /**
     * Scope a query for translated posts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTranslation()
    {
      // Find Translation Group ID
      $element = Translation::where('element_id', $this->ID)->first();

      // Find Translation collection
      return Translation::where('trid', $element->trid)->get();
    }

    // public function scopeTranslate($query, $lang = '')
    // {
    //   // Find Translation Group ID
    //   $element = Translation::where('element_id', $this->ID)->first();
    //
    //   // Find Translation collection
    //   $translations = Translation::where('trid', $element->trid)->where('language_code', $lang)->first();
    //   if (empty($translations)) {
    //     $translations = Translation::where('trid', $element->trid)->where('source_language_code', null)->first();
    //   }
    //
    //   // Getting Post Object
    //   $post =  Post::find($translations->element_id);
    //   // dump(['scopeTranslation ' => $element]);
    //   return $post;
    // }

    /**
     * Overriding newQuery() to the custom PostBuilder with some interesting methods.
     *
     * @param bool $excludeDeleted
     *
     * @return Wpml\PostBuilder
     */
    public function newQuery($excludeDeleted = true)
    {
        $builder = new PostBuilder($this->newBaseQueryBuilder());
        $builder->setModel($this)->with($this->with);
        // disabled the default orderBy because else Post::all()->orderBy(..)
        // is not working properly anymore.
        // $builder->orderBy('post_date', 'desc');
        if (isset($this->postType) and $this->postType) {
            $builder->type($this->postType);
        }
        if ($excludeDeleted and $this->softDelete) {
            $builder->whereNull($this->getQualifiedDeletedAtColumn());
        }
        // dump(['newQuery ' => $this]);
        return $builder;
    }

}
