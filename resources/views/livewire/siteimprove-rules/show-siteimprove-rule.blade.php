<div class="cwd-component">
    <div class="align-right">
        <x-forms.button.back :href="route('siteimprove-rules.index')">All Siteimprove Rules</x-forms.button.back>
    </div>

    <h1>
        {{ $id }}: {{ $issue }}
    </h1>

    <table class="table bordered">
        <tr>
            <th style="width: 200px">WCAG 2 criteria</th>
            <td>
                @foreach($criteria as $criterion)
                    <a href="{{ route('criteria.show', $criterion) }}">{{ $criterion->getLongName() }}</a>
                @endforeach
            </td>
        </tr>
    </table>

    <div>
        <h2>AI Prompt</h2>
        <aside class="panel">
            <button style="float:right" onclick="navigator.clipboard.writeText(document.getElementById('ai-prompt').innerText).then(() => {alert('Prompt copied to clipboard!')})">Copy to Clipboard</button>
<pre id="ai-prompt">
    ...
</pre>
        </aside>
    </div>

</div>
