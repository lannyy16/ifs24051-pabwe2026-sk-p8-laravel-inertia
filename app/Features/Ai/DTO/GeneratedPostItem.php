<?php
namespace App\Features\Ai\DTO;
class GeneratedPostItem { public function __construct(public readonly string $title,public readonly string $category,public readonly string $excerpt,public readonly string $content){} public function toArray():array{return ['title'=>$this->title,'category'=>$this->category,'excerpt'=>$this->excerpt,'content'=>$this->content];} }
