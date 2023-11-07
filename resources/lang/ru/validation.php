<?php

return array (
  'accepted' => ':attribute должен быть принят.',
  'active_url' => ':attribute не является допустимым URL.',
  'after' => ':attribute должен быть датой после :date.',
  'after_or_equal' => ':attribute должен быть датой после или равен дате.',
  'alpha' => ':attribute может содержать только буквы.',
  'alpha_dash' => ':attribute может содержать только буквы, цифры, тире и подчеркивания.',
  'alpha_num' => ':attribute может содержать только буквы и цифры.',
  'array' => ':attribute должен быть массивом.',
  'before' => ':attribute должен быть датой до :date.',
  'before_or_equal' => ':attribute должен быть датой до или равен дате.',
  'between' => 
  array (
    'numeric' => ':attribute должен быть между :min и :max.',
    'file' => ':attribute должен быть между :min и :max килобайт.',
    'string' => ':attribute должен быть между :min и :max символами.',
    'array' => ':attribute должен иметь значения между :min и :max.',
  ),
  'boolean' => 'Поле :attribute должно быть истинным или ложным.',
  'confirmed' => 'Подтверждение :attribute не совпадает.',
  'date' => ':attribute не является допустимой датой.',
  'date_equals' => ':attribute должен быть датой, равной :date.',
  'date_format' => ':attribute не соответствует формату :format.',
  'different' => ':attribute и :other должны быть разными.',
  'digits' => ':attribute должен быть :digits цифры.',
  'digits_between' => ':attribute должен быть между :min и :max цифрами.',
  'dimensions' => ':attribute имеет недопустимые размеры изображения.',
  'distinct' => 'Поле :attribute имеет повторяющееся значение.',
  'email' => ':attribute должен быть действительным адресом электронной почты.',
  'ends_with' => ':attribute должен заканчиваться одним из следующих: :values.',
  'exists' => 'Выбранный :attribute недействителен.',
  'file' => ':attribute должен быть файлом.',
  'filled' => 'Поле :attribute должно иметь значение.',
  'gt' => 
  array (
    'numeric' => ':attribute должен быть больше чем :value.',
    'file' => ':attribute должен быть больше, чем :value в килобайтах.',
    'string' => ':attribute должен быть больше чем :value символов.',
    'array' => ':attribute должен иметь больше чем :value элементов.',
  ),
  'gte' => 
  array (
    'numeric' => ':attribute должен быть больше или равен значению :value',
    'file' => ':attribute должен быть больше или равен :value в килобайтах.',
    'string' => ':attribute должен быть больше или равен символу :value.',
    'array' => ':attribute должен иметь элементы :value или более.',
  ),
  'image' => ':attribute должен быть изображением.',
  'in' => ':attribute выбранный недействителен.',
  'in_array' => 'Поле :attribute не существует в :other.',
  'integer' => ':attribute должен быть целым числом.',
  'ip' => ':attribute должен быть действительным IP-адресом.',
  'ipv4' => ':attribute должен быть действительным адресом IPv4.',
  'ipv6' => ':attribute должен быть действительным адресом IPv6.',
  'json' => ':attribute должен быть допустимой строкой JSON.',
  'lt' => 
  array (
    'numeric' => ':attribute должен быть меньше чем :value.',
    'file' => ':attribute должен быть меньше чем :value килобайт.',
    'string' => ':attribute должен быть меньше чем :value символов.',
    'array' => ':attribute должен иметь меньше чем: value элементов.',
  ),
  'lte' => 
  array (
    'numeric' => ':attribute должен быть меньше или :value равен',
    'file' => ':attribute должен быть меньше или равен :value в килобайтах.',
    'string' => ':attribute должен быть меньше или равен символу :value.',
    'array' => ':attribute не должен содержать больше, чем :value элементов.',
  ),
  'max' => 
  array (
    'numeric' => ':attribute не может быть больше, чем :max.',
    'file' => ':attribute не может быть больше, чем:max килобайт.',
    'string' => ':attribute не может быть больше, чем:max символов.',
    'array' => ':attribute может содержать не более :max.',
  ),
  'mimes' => ':attribute должен быть файлом типа: :value.',
  'mimetypes' => ':attribute должен быть файлом типа: :value.',
  'min' => 
  array (
    'numeric' => ':attribute должен быть не менее :min.',
    'file' => ':attribute должен быть не менее :min килобайт.',
    'string' => ':attribute должен содержать не менее :min Символов.',
    'array' => ':attribute должен содержать как минимум :min элементов.',
  ),
  'not_in' => ':attribute выбранный недействителен.',
  'not_regex' => 'Формат :attribute неверен.',
  'numeric' => ':attribute должен быть числом.',
  'password' => 'Неправильный пароль.',
  'present' => 'Поле: атрибут должно присутствовать.',
  'regex' => 'Формат :attribute неверен.',
  'required' => 'Поле :attribute обязательно для заполнения.',
  'required_if' => 'Поле :attribute является обязательным, когда: other is: value.',
  'required_unless' => 'Поле :attribute является обязательным, если: other не находится в :value.',
  'required_with' => 'Поле :attribute обязательное, если присутствует: значения.',
  'required_with_all' => 'Поле :attribute является обязательным при наличии: значений.',
  'required_without' => 'Поле :attribute является обязательным, если: значения отсутствуют.',
  'required_without_all' => 'Поле :attribute является обязательным, если нет ни одного из: значений.',
  'same' => ':attribute и :other должны совпадать.',
  'size' => 
  array (
    'numeric' => ':attribute должен быть :size.',
    'file' => ':attribute должен быть :size в килобайтах.',
    'string' => ':attribute должен быть :size символов.',
    'array' => ':attribute должен содержать :size элементов.',
  ),
  'starts_with' => ':attribute должен начинаться с одного из следующих значений: :value.',
  'string' => ':attribute должен быть строкой.',
  'timezone' => ':attribute должен быть допустимой зоной.',
  'unique' => ':attribute уже занят.',
  'uploaded' => 'Не удалось загрузить :attribute',
  'url' => 'Формат :attribute неверен.',
  'uuid' => ':attribute должен быть действительным UUID.',
  'custom' => 
  array (
    'attribute-name' => 
    array (
      'rule-name' => 'custom-message',
    ),
  ),
);
