<?php 

namespace App\Libraries;

class QueryBuilder {

    static private function qblock_and(&$q, $qblock) {
        if ($qblock->conditional === 'contains') {
            $q->where($qblock->column,'like','%'.$qblock->value.'%');
        } else if ($qblock->conditional === 'is_null') {
            $q->whereNull($qblock->column);
        } else if ($qblock->conditional === 'not_null') {
            $q->whereNotNull($qblock->column);
        } else if ($qblock->conditional === 'is_true') {
            $q->where($qblock->column,true);
        } else if ($qblock->conditional === 'is_false') {
            $q->where($qblock->column,false);
        } else {
            $q->where($qblock->column,$qblock->conditional,$qblock->value);
        }
    }
    static private function qblock_or(&$q, $qblock) {
        if ($qblock->conditional === 'contains') {
            $q->orWhere($qblock->column,'like','%'.$qblock->value.'%');
        } else if ($qblock->conditional === 'is_null') {
            $q->orWhereNull($qblock->column);
        } else if ($qblock->conditional === 'not_null') {
            $q->orWhereNotNull($qblock->column);
        } else if ($qblock->conditional === 'is_true') {
            $q->where($qblock->column,true);
        } else if ($qblock->conditional === 'is_false') {
            $q->where($qblock->column,false);
        } else {
            $q->orWhere($qblock->column,$qblock->conditional,$qblock->value);
        }
    }
    static public function build_where(&$q, $qblock) {
        $q->where(function ($q) use ($qblock){
            foreach($qblock->block as $qblock_out) {
                if ($qblock->and_or === 'or') {
                    $q->orWhere(function ($q) use ($qblock_out){
                        foreach($qblock_out->check as $qblock_in) {
                            if ($qblock_out->and_or === 'and') {
                                self::$qblock_and($q,$qblock_in);
                            } else if ($qblock_out->and_or === 'or') {
                                self::qblock_or($q,$qblock_in);
                            }
                        }
                    });
                } else if ($qblock->and_or === 'and') {
                    $q->where(function ($q) use ($qblock_out) {
                        foreach($qblock_out->check as $qblock_in) {
                            if ($qblock_out->and_or === 'and') {
                                self::qblock_and($q,$qblock_in);
                            } else if ($qblock_out->and_or === 'or') {
                                self::qblock_or($q,$qblock_in);
                            }
                        }
                    });
                }
            }
        });
    }
}