<form {{$attributes}}>
    <fieldset class="{{$legendtype??'default'}}">  {{-- default or semantic --}}
        <legend @if ($legend_sr_only??false == 'true') class="sr-only" @endif >{{$legend??'Form'}}</legend>
        {{$slot}}
    </fieldset>
</form>