<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Criterion;
use App\Models\Guideline;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GuidelineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guidelines = collect([
            [1, 'Images', '1.1.1 Non-text Content (A)', 'Informative and grouped images must contain alternative text describing the purpose or meaning of the image(s).'],
            [2, 'Images', '1.1.1 Non-text Content (A)', 'Decorative images must have empty alternative text or be otherwise hidden from assistive technology.'],
            [3, 'Images', '1.1.1 Non-text Content (A)', 'Functional images must have alternative text describing the input\'s purpose.'],
            [4, 'Images', '1.1.1 Non-text Content (A)', 'Complex images (graphs, maps, charts) must have a text description of all relevant information.'],
            [5, 'Forms and Inputs', '1.1.1 Non-text Content (A)', 'CAPTCHAs must be identified with alternative text.'],
            [6, 'Forms and Inputs', '1.1.1 Non-text Content (A)', 'CAPTCHAs which require user input offer at least two different modalities (e.g., visual and auditory).'],
            [7, 'Content', '1.1.1 Non-text Content (A)', 'Adjacent text and images which navigate to the same destination should be presented and announced as one link.'],
            [8, 'Multimedia', '1.2.1 Audio-only and Video-only (Prerecorded) (A)', 'Audio-only content must supply a text transcript. The location must be referenced in the accessible name of the audio content.'],
            [9, 'Multimedia', '1.2.1 Audio-only and Video-only (Prerecorded) (A)', 'Video-only content supplies either a descriptive text transcript or audio description.  The location must be referenced in the accessible name of the video content.'],
            [10, 'Multimedia', '1.2.2 Captions (Prerecorded) (A)', 'Multimedia content must have caption support for audio.  Captions must be accurate, must include dialogue, the individual speaking, and any relevant audio information.'],
            [11, 'Multimedia', '1.2.3 Audio Description or Media Alternative (Prerecorded) (A)', 'Multimedia content must supply a text transcript OR an audio description.'],
            [12, 'Multimedia', '1.2.4 Captions (Live) (AA)', 'Live multimedia content must be captioned.  Captions must be accurate, must include dialogue, the individual speaking, and any relevant audio information.'],
            [13, 'Multimedia', '1.2.5 Audio Description (Prerecorded) (AA)', 'Multimedia content must supply an audio description, which accurately informs the user of any important visual information not already conveyed through audio.'],
            [14, 'Structure', '1.3.1 Info and Relationships (A)', 'Landmarks present on the page are used to organize the correct type of content.  If landmarks are given names, they need to be uniquely named.'],
            [15, 'Structure', '1.3.1 Info and Relationships (A)', 'Wherever text appearance (i.e., bold, italics, size) is used to convey information, that information must also be presented in text alone.'],
            [16, 'Structure', '1.3.1 Info and Relationships (A)', 'Headings must have an appropriate relative hierarchy to other headings on the same page.'],
            [17, 'Structure', '1.3.1 Info and Relationships (A)', 'Page structure is implemented with HTML.  Visual appearance is implemented with CSS.  Meaningful HTML is not used to solely achieve a visual effect or appearance.'],
            [18, 'Forms and Inputs', '1.3.1 Info and Relationships (A)', 'Form inputs must have labels which are readable (programmatically determinable) by assistive technology.'],
            [19, 'Forms and Inputs', '1.3.1 Info and Relationships (A)', 'Form input groupings (i.e., related radio buttons, related checkboxes, related text inputs like First/Last name) are grouped semantically.'],
            [20, 'Content', '1.3.1 Info and Relationships (A)', 'Data tables are used to present tabular data.  Data tables must include table headers, which are associated with the correct table cells.  Descriptions for tables must be programmatically associated.'],
            [21, 'Content', '1.3.1 Info and Relationships (A)', 'Layout tables must not include table headers, captions, or summaries.  They should be marked with role="presentation".'],
            [22, 'Structure', '1.3.1 Info and Relationships (A)', 'All elements use the proper semantic roles, and contain all required parent and child elements. (e.g., a "list" must contain "listitem").'],
            [23, 'Structure', '1.3.1 Info and Relationships (A)', 'Element IDs do not repeat more than once per page.'],
            [24, 'Content', '1.3.1 Info and Relationships (A)', 'All content is available to (readable by) assistive technology.'],
            [25, 'Structure', '1.3.2 Meaningful Sequence (A)', 'The order in which content is presented in DOM must be logical.'],
            [26, 'Content', '1.3.2 Meaningful Sequence (A)', 'Whitespace is not utilized to create text spacing within a word.  Whitespace is not utilized to create columns or tables visually in plain text.'],
            [27, 'Content', '1.3.3 Sensory Characteristics (A)', 'Instructions for operating web-based content and cues for identifying content does not rely exclusively on color, shape, size, position, or sound. (Above/Below references are allowed.)'],
            [28, 'Resizing', '1.3.4 Orientation (AA)', 'Content is viewable in portrait and landscape device orientations, and the user is not prompted to switch orientation unless a specific orientation is essential.'],
            [29, 'Forms and Inputs', '1.3.5 Identify Input Purpose (AA)', 'The purpose of any form input about the user is identified in code when the purpose is defined in HTML (https://www.w3.org/TR/WCAG22/#input-purposes)'],
            [30, 'Color', '1.4.1 Use of Color (A)', 'Color may not exclusively distinguish between plain text and interactive text or distinguish one type of content from another without a 3:1 color contrast difference.'],
            [31, 'Color', '1.4.1 Use of Color (A)', 'Color may not exclusively identify content or distinguish differences in any content (e.g., red items are invalid, green items are valid).'],
            [32, 'Multimedia', '1.4.2 Audio Control (A)', 'Auto-playing audio that lasts longer than 3 seconds must be pausable OR have an independent volume control.'],
            [33, 'Color', '1.4.3 Contrast (Minimum) (AA)', 'Large-scale (24px or 19px bold) text must have a color contrast ratio of 3:1.  Logos, inactive components, and pure decoration are excluded.'],
            [34, 'Color', '1.4.3 Contrast (Minimum) (AA)', 'Non Large-scale text must have a color contrast ratio of 4.5:1. Logos, inactive components, and pure decoration are excluded.'],
            [35, 'Resizing', '1.4.4 Resize Text (AA)', 'Text can be resized up to 200% without page content disappearing or losing functionality.'],
            [36, 'Resizing', '1.4.4 Resize Text (AA)', 'Text can be resized up to 200% without text clipping through other elements.'],
            [37, 'Images', '1.4.5 Images of Text (AA)', 'Images of text are not used when the same presentation can be made with native HTML/CSS.  Logos and branding are excluded.'],
            [38, 'Resizing', '1.4.10 Reflow (AA)', 'Content may only scroll in one dimension (horizontal or vertical) at a width and height equivalent of 320x256 pixels or larger.  Excluded is content where a two-dimensional layout is necessary (video, data tables, maps, diagrams)'],
            [39, 'Color', '1.4.11 Non-text Contrast (AA)', 'Active user interface components must meet a 3:1 color contrast ratio.  (This includes buttons, inputs, custom focus indicators, dropdowns, checkboxes, and radio buttons.)'],
            [40, 'Color', '1.4.11 Non-text Contrast (AA)', 'Graphical objects which describe important content must meet a 3:1 color contrast ratio; except flags, real life imagery, branding, reference screencaps, and heatmaps.'],
            [41, 'Resizing', '1.4.12 Text Spacing (AA)', 'No content or functionality may be lost when text is set to: line spacing of 1.5x font size, letter spacing at 0.12x font size, word spacing at 0.16x font size, and paragraph spacing 2x the font size.'],
            [42, 'Keyboard', '1.4.13 Content on Hover or Focus (AA)', 'Content generated by hover or focus can be dismissed without moving hover or focus.'],
            [43, 'Keyboard', '1.4.13 Content on Hover or Focus (AA)', 'Content generated by hover or focus of an element can be hovered over without the content disappearing.'],
            [44, 'Keyboard', '1.4.13 Content on Hover or Focus (AA)', 'Content generated by hover or focus of an element does not disappear until dismissed, is no longer valid, or hover or focus is removed.'],
            [45, 'Keyboard', '2.1.1 Keyboard (A)', 'All interactive elements must be able to be navigated to and interacted with using a keyboard only.'],
            [46, 'Keyboard', '2.1.1 Keyboard (A)', 'Timing of keystrokes must not be required for interacting with any functionality.'],
            [47, 'Keyboard', '2.1.1 Keyboard (A)', 'Functionality available when hovering with a cursor must be available to keyboard input.'],
            [48, 'Keyboard', '2.1.2 No Keyboard Trap (A)', 'Focus that enters any element must be able to leave that element.  If the method requires more than ESC, Arrow Keys, or Tab, the user must be informed of the method.'],
            [49, 'Keyboard', '2.1.4 Character Key Shortcuts (A)', 'If any keyboard shortcut only requires letter, number, punctuation, or symbol characters, an option exists to turn it off, OR to remap it to include CTRL or ALT modifiers, OR it is only active on focus.'],
            [50, 'Motion', '2.2.1 Timing Adjustable (A)', 'Any time limit may be disabled, extended (with a 20-second warning), or adjusted; unless it is part of a current real-life event, it is essential, or it has a time limit of 20 hours or more.'],
            [51, 'Motion', '2.2.2 Pause, Stop, Hide (A)', 'Any moving, blinking, or scrolling information that starts automatically, lasts over 5 seconds, and is part of other content must include a pause, stop, or hide mechanism.'],
            [52, 'Motion', '2.2.2 Pause, Stop, Hide (A)', 'Any automatically updating content that starts automatically and is part of other content must include a pause, stop, or hide mechanism.'],
            [53, 'Motion', '2.3.1 Three Flashes or Below Threshold (A)', 'No content may flash more than 3 times per any 1-second period.'],
            [54, 'Keyboard', '2.4.1 Bypass Blocks (A)', 'Content which repeats on multiple webpages has a mechanism to skip over it.'],
            [55, 'Content', '2.4.2 Page Titled (A)', 'All pages have a title, which details the topic or purpose of the page.  Titles should be organized from most-specific to least-specific.'],
            [56, 'Keyboard', '2.4.3 Focus Order (A)', 'Focus order must follow a logical sequence. Tabindex values must not interfere with the proper tab sequence of the page.'],
            [57, 'Keyboard', '2.4.3 Focus Order (A)', 'Dialog content must gain focus immediately or as the next press of TAB once activated.  Dismissing dialog content returns focus to the trigger or to the next element in DOM after the trigger.'],
            [58, 'Content', '2.4.4 Link Purpose (In Context) (A)', 'Link destination is described by link text on its own or by link text programmatically associated with other text on the page (except where the destination is ambiguous to all users).'],
            [59, 'Structure', '2.4.5 Multiple Ways (AA)', 'Two or more mechanisms of finding a webpage are available, unless the page is accessed as part of a step in a process.'],
            [60, 'Content', '2.4.6 Headings and Labels (AA)', 'Headings that exist describe the topic or purpose of the content following after.'],
            [61, 'Forms and Inputs', '2.4.6 Headings and Labels (AA)', 'Each label describes the purpose of its associated input.'],
            [62, 'Keyboard', '2.4.7 Focus Visible (A)', 'Every focusable element has a focus indicator.'],
            [63, 'Keyboard', '2.4.11 Focus Not Obscured (Minimum) (AA)', 'Elements that currently have focus may never be hidden by other elements (such as a sticky header).'],
            [64, 'Interaction', '2.5.1 Pointer Gestures (A)', 'All functionality that uses multipoint or path-based gestures can be operated with a single pointer without a path-based gesture, unless a multipoint or path-based gesture is essential.'],
            [65, 'Interaction', '2.5.2 Pointer Cancellation (A)', 'Functionality operated with a single pointer must: not fire on the down event; fire on the up-event along with a way to abort or undo; reverse the function on the up-event, or complete an essetial function on the down event.'],
            [66, 'Forms and Inputs', '2.5.3 Label in Name (A)', 'For user interface components with labels that include text or images of text, the accessible name contains the text that is presented visually.'],
            [67, 'Interaction', '2.5.4 Motion Actuation (A)', 'Any functionality activated by device motion can be performed with a user interface component that does not require motion.'],
            [68, 'Interaction', '2.5.4 Motion Actuation (A)', 'Any functionality activated by device motion can be disabled.'],
            [69, 'Interaction', '2.5.7 Dragging Movements (AA)', 'Any functionality that can be achieved by dragging (click and hold then move) must be operable without the need for dragging.'],
            [70, 'Interaction', '2.5.8 Target Size (Minimum) (AA)', 'All elements must have a clickable target size of at least 24x24 pixels unless the element is inline, controlled by the browser, or the "target offset" to all adjacent clickable elements is at least 24px.'],
            [71, 'Structure', '3.1.1 Language of Page (A)', 'The default language of each page must be defined in the html tag.'],
            [72, 'Structure', '3.1.2 Language of Parts (AA)', 'Text in a different language than the page default must be identified in HTML; aside from proper names, technical terms, words without a defined language, and words that are part of the vernacular of the immediately surrounding text.'],
            [73, 'Keyboard', '3.2.1 On Focus (A)', 'When a user interface component gains focus, it may not trigger a change of context.  (e.g., on focus must not submit a form, launch a new window, cause an immediate change of focus, or change the purpose of any page content.)'],
            [74, 'Interaction', '3.2.2 On Input (A)', 'If a change of context is triggered by a change in a setting or value of a user interface component (for example, changing the option of a select box), the user must be warned beforehand.'],
            [75, 'Structure', '3.2.3 Consistent Navigation (AA)', 'Navigations which are utilized on multiple pages keep the same relative order on all pages, unless the user initiates the change. (Items can be removed or added, but they must maintain the same order relative to each other.)'],
            [76, 'Structure', '3.2.4 Consistent Identification (AA)', 'Any components with similar functionality used on multiple pages must be labeled identically and function identically. (e.g. a header Search field must be labeled the same on all pages)'],
            [77, 'Structure', '3.2.6 Consistent Help (A)', 'Help mechanisms such as contact details, messaging, chat, or self-help options must be in the same relative order on all pages where the information is present.'],
            [78, 'Forms and Inputs', '3.3.1 Error Identification (A)', 'Whenever an input error is detected through validation, the user is informed of the error, and what was incorrect in the input.'],
            [79, 'Forms and Inputs', '3.3.2 Labels or Instructions (A)', 'Visible labels or instructions are available for all inputs and input groupings.'],
            [80, 'Forms and Inputs', '3.3.2 Labels or Instructions (A)', 'Labels describe any required fields or required formatting requirements.  (e.g. If a MM/DD/YYYY format is required)'],
            [81, 'Forms and Inputs', '3.3.3 Error Suggestion (AA)', 'If an input error was detected due to a blank input on a required field, the user is informed of the fields which were left blank in the error message.'],
            [82, 'Forms and Inputs', '3.3.3 Error Suggestion (AA)', 'If an input error was detected due to an input that did not follow required formatting, the user is informed of the proper formatting in the error message.'],
            [83, 'Forms and Inputs', '3.3.3 Error Suggestion (AA)', 'If an input error was detected that was outside of the allowable range of values, the user is informed of the proper range in the error message.'],
            [84, 'Forms and Inputs', '3.3.4 Error Prevention (Legal, Financial, Data) (AA)', 'Any legal or financial data input must be reversible, validated for input errors, or include a mechanism for reviewing, confirming, or correcting information before submission.'],
            [85, 'Forms and Inputs', '3.3.7 Redundant Entry (A)', 'Users are not required to refill the same information in the same process unless it is available as a selection, is essential, is ensuring security, or the original information is no longer valid.'],
            [86, 'Forms and Inputs', '3.3.8 Accessible Authentication (AA)', 'Login processes must not solely rely on cognitive tests.  All steps in a login process must support some method that does not rely on memory or knowledge.'],
            [87, 'Structure', '4.1.2 Name, Role, Value (A)', 'All elements have appropriate accessible names and roles assigned.'],
            [88, 'Multimedia', '4.1.2 Name, Role, Value (A)', 'Video and audio content or iframes which present user-readable content require a title or description.'],
            [89, 'Structure', '4.1.2 Name, Role, Value (A)', 'All custom functionality utilizes the appropriate ARIA features for states, properties, and values. (e.g. aria-expanded, aria-haspopup)'],
            [90, 'Motion', '4.1.3 Status Messages (AA)', 'Status messages which change without a page reload notify users of assistive technologies without requiring the user to send focus to the message.'],
        ])->map(function ($guideline) {
            return [
                'number' => $guideline[0],
                'category' => $guideline[1],
                'criteria' => $guideline[2],
                'description' => $guideline[3],
            ];
        });

        // Create distinct criteria from the guidelines
        $criteria = $guidelines->pluck('criteria')->unique()
            ->mapWithKeys(function ($criterion) {
                $matches = [];
                preg_match('/^(\d+\.\d+\.\d+) (.+) \((.+)\)$/', $criterion, $matches);
                return [$criterion => Criterion::factory()->create([
                    'number' => $matches[1],
                    'name' => $matches[2],
                    'level' => $matches[3],
                ])];
            });

        // Get a lookup table of the category names to category ids
        $categories = Category::all()->keyBy('name');

        // Store the guidelines
        $guidelines->each(function ($guideline) use ($criteria, $categories) {
            Guideline::factory()->create([
                'number' => $guideline['number'],
                'name' => $guideline['description'],
                'criterion_id' => $criteria[$guideline['criteria']]->id,
                'category_id' => $categories[$guideline['category']]->id,
            ]);
        });
    }
}
