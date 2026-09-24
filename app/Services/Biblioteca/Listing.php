<?php

namespace App\Services\Biblioteca;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class Listing
{
    public static function perPage(Request $request): int
    {
        $value=filter_var($request->query('per_page',25),FILTER_VALIDATE_INT);
        return in_array($value,[25,50,100,150],true)?$value:25;
    }

    public static function sorting(Request $request,array $allowed,string $default,string $direction='asc'): array
    {
        $key=$request->query('sort',$default);
        if(!is_string($key)||!in_array($key,$allowed,true))$key=$default;
        $order=$request->query('direction',$direction);
        return [$key,in_array($order,['asc','desc'],true)?$order:$direction];
    }

    public static function paginate(array $rows,Request $request,callable $value,callable $identity,string $direction): LengthAwarePaginator
    {
        usort($rows,function($a,$b)use($value,$identity,$direction) {
            $left=$value($a);$right=$value($b);
            $comparison=is_numeric($left)&&is_numeric($right)?$left<=>$right:strnatcasecmp(Content::normalize((string)$left),Content::normalize((string)$right));
            return ($comparison?:strcmp((string)$identity($a),(string)$identity($b)))*($direction==='desc'?-1:1);
        });
        $size=self::perPage($request);
        $page=min(max(1,$request->integer('page',1)),max(1,(int)ceil(count($rows)/$size)));
        return new LengthAwarePaginator(array_slice($rows,($page-1)*$size,$size),count($rows),$size,$page,
            ['path'=>$request->url(),'query'=>$request->query()]);
    }
}
