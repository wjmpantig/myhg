<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'MyHG') }}</title>


        <link rel="stylesheet" type="text/css" href="css/app.css">
    </head>
    <body class="print">
                
            
        
        <table class="table is-bordered is-striped is-narrow">
            <thead>
                <tr>
                    <td rowspan="2">
                        Name
                    </td>
                    @foreach($labels as $label=>$dates)
                    <td colspan="{{count($dates)}}">
                        {{$label}}
                    </td>
                    @endforeach
                </tr>
                <tr>
                    @foreach($labels as $label=>$dates)
                        @foreach($dates as $date)
                    <td>
                        {{$date}}
                    </td>
                        @endforeach
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @for($i=0;$i<$students->count();$i++)
                <tr>
                    <td class="name" nowrap>{{$students[$i]->name}}</td>
                    
                </tr>
                @endfor
            </tbody>
        </table>

    
    </body>
</html>
