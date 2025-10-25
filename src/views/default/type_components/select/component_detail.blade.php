<?php
if (isset($form['datatable']) && $form['datatable']) {
    $datatable = explode(',', $form['datatable']);
    $table = $datatable[0];
    $field = $datatable[1];
    $record = CRUDBooster::first($table, ['id' => $value]);
    echo $record ? $record->$field : '';
}
if (isset($form['dataquery']) && $form['dataquery']) {
    $dataquery = $form['dataquery'];
    $query = DB::select(DB::raw($dataquery));
    if ($query) {
        foreach ($query as $q) {
            if ($q->value == $value) {
                echo $q->label;
                break;
            }
        }
    }
}
if (isset($form['dataenum']) && $form['dataenum']) {
    echo $value;
}
?>