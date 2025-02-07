Below are the planning and meeting notes for the Web Accessibility Review AI Project. The notes are organized by date and include the project plan, meeting notes, and demo notes. The notes provide an overview of the project's progress, goals, and feedback from stakeholders.

---  

# Web Accessibility Review Process Improvement with AI

**Primary Goal**: Reduce the manual effort in web accessibility reviews by leveraging AI.

**Secondary Goal**: Build in-house expertise and tools for AI integration in Custom Development projects.

## Background
The WCAG Checklist and Cornell’s WA Testing Guidelines provide a robust framework for website accessibility reviews. While they have been successfully used and improved by Custom Development for several years, they remain time-consuming and can be challenging to use (see WA Checklist Review & Feedback).

Custom Development is exploring the possibility of incorporating AI into a web application version of the WCAG checklist. AI could be used to apply the knowledge in the Guidelines document to articulate failed criteria and provide recommendations for addressing review findings. This could significantly speed up the review process and improve reporting for clients. It could also serve as the foundation for providing an externally-facing tool for anyone engaged in digital accessibility work across the university.

As an internally-used tool, the web application would be a low-risk deployment with rapid development and feedback cycles. This would allow Custom Development to safely experiment with AI while expanding and refining site-building tools and libraries. It would provide the groundwork for Custom Development to offer AI integration services in the future and create a platform for a university-wide accessibility tool.

## Approach
A rapid prototyping approach will be employed to create a minimum viable product (MVP) in order to quickly learn what is effective. A small team will initially scope the features based on the review and feedback for the current checklist. The initial prototype will test the user experience and integrate preliminary AI features. If the prototype is viable, an iterative approach will be applied to reach a level of usability and effectiveness that surpasses the current process.

Once the web application has been validated, the team will develop a roll-out strategy for the full release. These plans will include sustained support for the web application, development of an AI integration services offering, and an initial review of next steps toward developing an externally-facing tool.

## Evaluation
The success of this project will be measured by the reduced effort required for web accessibility reviews. Custom Development’s historical data on WA reviews will serve as the baseline for assessing efficiency gains from the web application.

Additionally, insights gained from development will inform Custom Development’s readiness to offer AI integration services and build a tool that can be utilized across the university.

## Resources
Historically, the development of internal tools, such as the WCAG checklist and Custom Development’s software libraries, has occurred opportunistically as part of client projects and as team time has allowed. This results in slow development and unpredictable support.

This proposal requests investment in the initial application development and support, positioning Custom Development to benefit from ongoing cost savings and additional service offerings.


---

# Project Plan

**Date:** Friday, August 09, 2024, Updated continually throughout the project (showing final version)

## Goal
Develop a minimum viable product that simplifies completing a WCAG Checklist by using AI to suggest applicable guidelines for accessibility issues in a website accessibility review.

## Approach
Iterate through short release and review cycles to prove functionality and utility, obtaining rapid feedback. Use a simplistic Agile Scrum approach for planning, coordination, and feedback.

## Team
- **Key Stakeholders:** Annie, Sean, Ryann
- **Scrum Master/PC:** David
- **Project Champion:** Nick
- **Lead Developer:** Eric
- **Developer:** Rich
- **UX Designer:** Dawn

## Sprints
- Light planning on Monday morning (Devs + SM)
- Kanban board for task management
- Slack Teams standup for blockers + tasks (Devs + SM)
- Friday demos (All) + brief retrospective (Devs + SM + Nick)

## Releases / Milestones

### R1
- **Objective:** Provide AI with the guidelines document and a description of a WCAG issue; have AI recommend applicable guidelines.
- **Team:** Eric, Rich (review), Dawn (review)

### R2
- **Objective:** Deploy a simplistic web app that encapsulates the functionality of R1. Provide to the WA team for initial testing.
- **Team:** Eric, Rich, Dawn (UX design + review), WA team (review)

### R3
- **Objective:** Store project information and results, and report on them for a site review. Include UX input in the design and testing. Deploy to a test site and use it on a WA review project.
- **Notes:** The reporting side will depend on further conversations, so this release should focus on the input process.
- **Team:** Eric, Rich, Dawn (UX design + review), WA team (review)

### R4
- **Objective:** Address additional usability issues reported in the WA Checklist Review & Feedback document. Test with more stakeholders and team members.
- **Revisions:**
    - Focus on Guideline checklist process.
    - GPT-01-preview enables generating procedural rules for analyzing the applicability of guidelines.
- **Team:** Eric, Rich, Dawn (UX design + review), WA team (design + review)

### R5 (New)
- **Objective:** Integrate AI into a Guidelines checklist, based on a POC for analyzing a page and examining elements identified in the guidelines as needing review.
- **Team:** Eric

### R6 (Previously R5)
- **Objective:** Deliver results of a site review in a format equivalent to the current checklist process. Include WA team in defining the format.
- **Team:** Eric, Rich, Dawn (UX design + review), WA team (design + review)

### R7 (Previously R6)
- **Objective:** Documentation + support setup, project report, and custom Dev demo / demo day.
- **Team:** Eric, Rich, Dawn (review), WA team (review)


---  

# Meeting Notes - August 30

## Eric's Screen Share
- **Azure OpenAI Studio**
    - Resource set up: **WA-Review-AI**
        - Working in a separate space directly for himself
            - Uncertainty on whether it affects other instances
        - Working with "Bring Your Own Data"
            - Drops into the chat
            - Utilizing **GPT-4o-mini**
        - Need to add data source
            - **Azure AI Search** (No need for a file)
            - Nick's "Stuff" can be deleted now
            - Eric to remove extra search indexes
        - Utilizing the **WA-Guidelines** (Included in the data source filter)
            - API Key for data connection
            - Save and close
        - Now the data source will be in the chat playground
            - Contains a generic system message

## Step-by-Step Guide - Azure AI Notes
- **Question:** When we set up a new app, will we have to go through this process every time?
    - The piece we are really setting up is the AI search resource
        - The playground is where we conduct the setup and testing

## Continued Demo
- Eric has developed a prompt to submit:
    - Everything after the first sentence describes interactions and steps taken
        - Language is fine-tuned for formatted input
- **Section on Parameters:** Rich continuing to research this subject
    - Experimenting with **Temperature** and **Top P**
        - Reducing Top P decreases randomness (avoids overly creative responses)
- Tested various fields in the **Lab of Ornithology AA Checklist**
    - Results have been well-formatted
    - 4.5/5 rating for applicable guidelines
    - Can follow up with direct details on the guidelines:
        - Includes Description, Remediation, and Testing recommendations
    - Language around the description has been good
    - Remediation Recommendations are mainly on point:
        - Sometimes vague
        - Possible direction: Provide the client a read-only interface to these reviews
            - Utilize AI to assist them in completing their work
- Responses vary slightly each time:
    - 4/5 accuracy for guidelines
        - Occasionally, one on the list isn't correct
- **Field "View Code":**
    - Available in several formats
        - JSON and Curl have been most useful
    - Once we have the shell code, we can transfer it to **PHP Storm (Dev environment)**
        - Running the curl command from the shell code
- **Currently Working On:**
    - OpenAI PHP client
        - In theory, this would work with OpenAI
            - No proper response from Azure yet

## Feedback
- **Nick:**
    - Looks good
    - Will delete his apps to avoid charges
- **Rich:**
    - Looks good
    - Questions on integration and structured approach
- **Eric:**
    - Very pleased with information retrieval
        - Even from vague statements
        - Imagines an interface that parcels this information and allows for button integration for quick select options
            - Example: Click 3.3.1 for further understanding

## Additional Comments
- **Rich:**
    - Present the issue for the user to fix
        - Related information describing the problem and possible solution
        - Application manages the list of issues and resources for resolution
- **Eric:**
    - Next week, we will test real UI approaches
        - Dawn will be able to experiment
        - At the end of the week, we will demo with Annie and Shawn
    - Had a conversation with Annie and Sean:
        - Identifying available Google Doc numbers
            - No constraints on the way data is chunked


---  

# Planning Meeting Notes - September 3

## Discussion Points

### 1. WA AI Project Instances
- Found two instances of the WA AI Project in Replicon:
    - **WA AI Tool**
    - **CIT - WA Assessment AI Application**
        - **Action Item:** Inform Dawn to change time over to **CIT - WA Assessment AI Application**.
            - Delete the other instance.
        - Dawn is out this week.
            - Nick will touch base with Ryan to make the changes in Replicon.

### 2. R2 – Web App Deployment
- **Objective:** Deploy a simplistic web app that encapsulates the functionality of R1 and provide it to the WA team for initial testing.
    - **Team Members Involved:** Eric, Rich, Dawn (UX design + review), WA team (review).

### 3. Team Collaboration
- The team switched to the board:
    - **Rich's Tasks:**
        - Compare if the API is the same and check different results.
            - Eric experimented with the API in the blocked task.
            - Rich would like to understand how to divide up the work.
    - **Copilot Enterprise Experimentation:**
        - Not the decision we are going to proceed with.
            - Marking deleted.
    - **AI Identifying WCAG Criteria:**
        - Successfully tested; marking completed.
    - **Convert Guidelines Document:**
        - Still not a priority.
    - **R2 – Laravel App Connection to AI API:**
        - Assigned to Rich.
        - Has a repository in GitHub.
            - Eric will assist Rich with access.
    - **R2 – Simple UI for AI Interaction:**
        - This will be included in the app.
            - Provide fields for user input and AI response.
            - Create a simplistic chat feed for additional input and response.
                - **Goal:** Bare bones, simple to get into the WA hands for testing and feedback.
                    - On track for value.

### 4. Review Preparation for WA Team
- **Objective:** Determine what would make for a successful review and feedback from the WA team.
    - Provide instructions, questions to consider, and guidance on how and when to provide feedback.

### 5. UI Ideas for AI Interactions
- **Task for Dawn:** Brainstorm UI ideas for AI interactions since she is currently out.

### 6. Azure Budget Management
- **Discussion Points:**
    - Need to get more detailed information on managing the Azure budget.
    - Compare local processes vs. interacting with Azure AI.
    - Understand the forecasted budget.
    - Nick will find out what the budget is.
        - For experimentation purposes, the forecast is not a concern.
        - Ensure long-term viability works.
            - Figure out if we are on the right track.

### 7. GitHub Repository Creation
- **Task Assigned:** Create GitHub repo.
    - Assigned to Eric.


---  

# Planning Meeting Notes - September 9

## Discussion Points

### 1. Introductions
- Quick introductions from all attendees.

### 2. Week 2 Overview
- Week 2 is extending into this week.
- Anticipate some messiness due to splitting tasks among three different threads:
    - **1st Thread:**
        - Get existing code to the WA team for them to familiarize themselves.
            - Aim for a rough version for them to play around with.
            - **Action Item:** Gather feedback from the WA team on the Kanban board.
    - **2nd Thread:**
        - Brainstorm UI ideas for AI interactions.
            - Use this chat to understand what the AI knows and can figure out.
            - Consider involving Rich and Eric's experiences in brainstorming.
    - **3rd Thread:**
        - Store project information and results for site reviews.

- These three threads will need to be divided among Eric, Dawn, and Rich.
    - This week will involve splitting between R2 tasks and brainstorming discussions.
    - Initial elements of R3 have been figured out.

### 3. Updates from Rich
- Attempted to connect Eric’s code to the Azure index he has.
    - Eric has not tested it with the larger server yet.
        - Could be useful for future testing.
- Rich can use Eric’s code in LM Studio.
    - Planning to use the Llama model for additional testing.
- Reviewed some prompt engineering concepts around building games (e.g., Jeopardy).
    - Might be beneficial for specific interactions.
    - Eric prefers more structured interactions.
        - **Action Item:** Link to the brainstorming session.

### 4. Continued Discussion
- Desire to discuss further with Ayham:
    - Explore the current direction and potential optimizations.
        - Ayham inquired about the number of pages in the guidelines.
            - **Response:** 65 pages.
- Rich shared that working with the Azure interface has been complicated and difficult.
    - Wants to test out other models.
- **Meeting with Ayham:**
    - Does Ayham have time this week for a walkthrough?
        - **Response:** Yes, David will schedule the meeting.
- Rich will be out on Wednesday (FYI).
- This week is focused on gathering ideas, brainstorming, and activities.
- For Dawn:
    - Join meetings and provide feedback.
        - **Action Item:** Review prep for WA team and provide feedback.


---

# Meeting Notes - Ayham Boucher - September 10

## Discussion Points

### 1. Context and Initial Work
- Eric shared his screen to provide Ayham with context on the ongoing projects.
- Using the chat playground for initial work with semi-formatted responses.
    - Goal: Build a lightweight application.

### 2. Experience with Azure and OpenAI
- Minimal experience gained with Azure and OpenAI.
    - Rich has begun testing the Llama model.
- Eric expressed concerns about resource usage:
    - Uncertainty about potential inefficiencies in resource utilization.

### 3. Azure Interface Feedback
- Rich remarked that the Azure interface resembles a system interface for larger-scale projects:
    - Complex and heavyweight for current needs compared to LM Studio.
    - Question raised: Is this the right direction for the project?

### 4. Ayham’s Insights
- Ayham discussed the WA testing:
    - Application inputs include guidelines and the Excel sheet checklist.
        - Aim: To articulate an issue in words and identify applicable guidelines.
- Emphasized that AI is the right path for this work.
- Three categories for AI usage:
    - **LLM Access:**
        - Ask GPT questions, combining user queries and system prompts.
        - GPT can generate guideline responses without needing Azure search.
            - Tools can vectorize documents but have limited controls.
            - The context retrieval aspect is identified as a weak point.

### 5. RAG Explanation
- Ayham shared his screen to explain RAG (Retrieval-Augmented Generation).
    - Showed RAG code and provided examples:
        - Not using a vector database recommended.

### 6. Token Recommendations
- Recommended using tokens for monitoring usage:
    - Count tokens for files and queries.
    - Suggested using the OpenAI tokenizer to estimate the number of tokens:
        - Example: 26,844 tokens.
            - Can estimate cost per query.
    - Anything under 100,000 tokens is generally acceptable; models typically do not miss context in this range.

### 7. Collaboration Opportunities
- Ayham can share code with Eric.
- Chunking guidelines per title:
    - GPT mini can assist in organizing content.
        - Start with entire guidelines and optimize later.

### 8. Prompt Engineering Tips
- **Tip:** After writing prompts, utilize Claude (Ayham can provide access).

### 9. Questions and AI Interaction
- Eric inquired about sending large chunks of guidelines:
    - Ayham shared his screen again to demonstrate the client and chat UI.
- Need for the AI to access the entire content:
    - Even if chunked, GPT mini can facilitate this.
- Ayham has developed a splitter:
    - This tool uses GPT mini to divide text while maintaining control over the document.


---  

# Demo Notes - September 13

## Discussion Points

### 1. Demo Overview
- **Demo Started:** Eric shared his screen.
    - Last week, Eric focused on refining the first pass of the prompt.
        - Tested it against the AA Checklist.
        - Evaluated observed functionalities and integrated them into the chat box.
    - Still needs to enhance the structure.
        - Aiming for a consistent and organized layout.
    - Eric is pleased with the responses generated.

### 2. Feedback from the Group
- **Dawn:**
    - Received the guideline number during testing, which she found very useful.
    - Suggested that if a user leaves and returns, they may feel stuck.
        - **Recommendation:** Implement a CTA (Call to Action) to allow users to start over.

- **Annie:**
    - Has tested the new user terminology; it is functioning well.
    - Likes the idea of building out the interface for practical use.
    - Eric inquired whether the report to the user could link to the version of the review items.
        - Suggested having an AI chatbot that understands specific issues and provides guidance.

- **Rich:**
    - Initially worked with the HTML of the page.
        - It helped in some cases but not in others, as some went into the page's context.

- **Sean:**
    - Guidelines reported were relevant, and the order was appropriate.
        - The top guideline was deemed the most appropriate.
    - Identified a critical error:
        - When reporting non-failure issues, it still displayed guidelines as if they were failures.
            - **Action Item:** Needs adjustment to distinguish between failures and non-failures.
        - Eric expressed a desire to tweak the prompt to better answer user questions.
            - Suggestion to use the WA Slack channel to gather questions.
            - Aim for the system to understand user queries, not just follow a structured response.
            - Consider removing the step guide.
        - Annie highlighted a section asking, "Is this an Accessibility failure?" as helpful for new users.

### 3. Future Interface Plans
- Eric prefers an interface over a chat conversation.
    - Intent to transform the current guideline process into an AI-guided process.
    - Looking to develop an AI page that consolidates all information.
        - **Goal:** Enhance speed and efficiency for users.
    - Users could click a button (e.g., "Assessment") while AI operates in the background.
    - This proposal has not yet been reviewed or adapted.
        - Focus on building a data structure to address the AA Checklist.
        - Moving away from individual guideline selection.
    - Annie expressed support for this format, noting it needs adjustments for new users.

### 4. WA Review Page Suggestions
- **Dawn:** Suggested implementing breadcrumbs for navigation.
    - Example: If a user clicks into "All guidelines" then "Images," there should be a breadcrumb to return to "All guidelines."
- **Eric:** Mentioned the page is a clean slate and subject to change.
    - Further formation and updates are expected next week.


---  

# Planning Meeting Notes - September 16

## Discussion Points

### 1. Brainstorm Session
- **Additional Brainstorm Session:**
    - Meeting scheduled for later today.

### 2. Review and Reporting Format
- **Prompt Review:**
    - Need to understand what changes to make.
- **Meeting with Sean and Annie:**
    - Discuss Review/Report format.
    - **Dawn's Involvement:**
        - Dawn would like to be included in this meeting.
        - She sketched a concept on Friday unrelated to the prompt.
            - This might inspire thoughts on application usage.

### 3. WA Review Process
- **Current Topic:** How to conduct a WA review.
    - Added to the planning document: The reporting side will depend on further discussions, so this release should focus on the input process.
    - Opportunity to contextualize around the documentation.
    - The guidelines document serves as a framework for generating specific outcomes.
        - Key questions: What should we gather and report?
        - Meeting with Sean and Annie will address these questions.
- **Rich's Input:**
    - Guidelines will help generate identified issues.
        - Users should follow guidelines to determine what to look for.
        - Assurance: We are not losing the guidelines.
    - **Dawn's Contribution:**
        - Her sketch will emphasize Rich's points.

### 4. UX Considerations
- **User Experience Perspective:**
    - What tools and information should we provide for a quick and easy process?
        - Include this topic in today’s brainstorm session.

## Backlog Items
- **Eric's Backlog List:**
    - Understanding how to work with AI is essential.
    - Approach: Maintain an MVP (Minimum Viable Product) mentality.
        - Explore how to implement the AI chat version with the new interface.
- **Backend Code Review:**
    - Once terminology is settled, a backend code review will align titles.
- **WCAG 2.2:**
    - Uncertainty regarding the direction to take.
        - Request for more context; there is a lot of information available online.
- **Starter Kit Text Area:**
    - Assigned to Rich and added to this week’s tasks.
        - Rich noted a need for a rich text area.
            - Discussion required on integrating those libraries.
- **Caleb UI Elements:**
    - Rich plans to buy his own copy, which may be useful for this project.

### 5. App Review Readiness
- **Usability for Review:**
    - Can we prepare the app to be usable enough for someone to conduct a review?
        - Potential for Sean or Annie to perform a parallel review.


---  

# WA Review and Reporting Discussion Notes, Sept 17.

## General Discussion Points

### 1. AI Prompt Playground
- **Eric:** Demonstrated the AI Prompt Playground.
    - Shared updates regarding the prompt.

### 2. Reporting Expectations
- **Key Questions:**
    - What should we be reporting from the web application?
    - The app serves multiple needs for setting up projects and client presentations.

### 3. Contextual Information
- **Guidelines Overview:**
    - We have 90 items based on testing rules and numbered guidelines.
    - Explore the wealth of information available beyond the Cornell WA Testing Guidelines.
    - Defining relevant guidelines will lead to recommendations based on curated information.

### 4. Web Interface Responses
- **Expected Responses:**
    - **Annie:**
        - The success criteria should be the formal number included in the output.
            - It should remain strongly tied to the larger context.
        - A section on "How to test for this?" with links to guidelines could be useful.
            - Internal numbering should not be included in the output to avoid confusion for external users.

    - **Sean:**
        - Agreed that success criteria should be the output.
        - Guideline numbers originated from a limited number of sections (using Google Sheets).
            - Sorting tools are necessary.
        - Believes we should minimize the use of numbers unless technically required.

### 5. Scope Considerations
- **Defining Completion:**
    - How do we know when we have finished with a particular page?
    - **Rich:**
        - Emphasized the need to track considerations for each page.
        - Suggested removing the numbering system.
        - Proposed using AI to determine which guidelines to focus on based on page context.
            - Example: Identify applicable guidelines, e.g., "These 15 are applicable..."

### 6. User-Friendliness and Testing
- **Walkthrough Example:**
    - Eric conducted a demonstration.

- **Testing Methods:**
    - Link to old guidelines for reference.
    - Steps to reproduce issues should be clear.
    - Use current guidelines text to generate information.
    - Provide options for users to investigate on their own.
        - **Rich:** Mentioned that the structure should be able to parse through HTML.

### 7. Balancing Complexity
- **Eric's Focus:**
    - Striving to simplify processes for users.
    - Identifying tasks that AI can handle and articulate.

- **Detailed Guidelines vs. List View:**
    - A detailed 50-page guideline may not be suitable for all users.

## Next Steps
- Increase testing efforts and request more feedback.
- Link the chat to enable the AI to reach beyond the guidelines document.


---

# Demo Notes - September 20

## Demo Overview

### 1. AI Prompt Playground
- **Demonstration:**
    - Set up for everyone to explore and interact with the prompt being used.
    - Purpose: Allow testing without the need for coding.

### 2. Significant Gains This Week
- **Targets:**
    - Website improvements.
    - Description section enhancements.
    - "AI Help" feature to populate guidelines:
        - AI provides a JSON-formatted response.
        - Introduction of an AI Assistance Chat:
            - Users can see everything submitted, including debugging info used to populate guidelines.
            - Future feedback from the team will be requested.
                - Initial tests did not yield the best feedback.
                - A process will be established to assess the tool's usefulness.
                - The tool will continue to populate the same guidelines.

### 3. Current Performance
- Overall, the AI is providing guidelines accurately at this stage.
- **Eric's Suggestion:**
    - Team members should engage with the guidelines and AI Assistance Chat.
- **Structural Considerations:**
    - Eric is focusing on the structure, beginning with scope.
        - One of the scopes is general/overall.
            - Everything is contextualized based on its location.
        - Feedback on scope is not needed yet.
            - More details will be shared next week.

## Questions and Feedback
- **Rich:**
    - As the scope list develops, a list of considered guidelines will be necessary.
        - This will focus on RAG (Retrieval-Augmented Generation).

- **New Release of Chat-GPT O1:**
    - A more thoughtful approach has been implemented.
    - Eric has begun testing this new tool.
        - It recommended the creation of a smart checklist based on page analysis.

- **AI Assistance Chat:**
    - Eric feels optimistic about its current state, nearing more thorough testing.

- **Flux Consideration:**
    - Uncertainty remains regarding how to address flux.
    - The Rich Text Editor will still be required.


---  

# Planning Meeting Notes - September 23

## Discussion Points

### 1. R3 Overview
- **Focus:** Getting functionalities operational.
    - **Outstanding Tasks:**
        - Gathering feedback.
        - Closing out R3 for feedback.
            - **Action Item:** David will schedule a meeting with WA SMEs (Scheduled for Wednesday morning).
                - Purpose: Conduct a sanity check to ensure proper execution.
                - Note: Adjustments from this feedback will not alter the overall R3.

- **Textarea Field:**
    - Placed into blocked status.
        - **Flux Tool:**
            - A set of forum-related user interface elements that incorporate WA features as part of the basic structure.
            - **Website:** fluxui.dev
            - **Cost:** Currently $279 (Unlimited use, on sale).
                - Requires Ryan's approval for this project.
            - This tool could significantly enhance our current work and be useful for other projects as well.

- **Rich Text Editor:**
    - Added to R4.

### 2. R4 Development
- **Progression:** Transitioning from an acceptable solution to an improvement phase.
    - **Goal:** Real application use.
        - Seeking substantial feedback this week.

- **UX Review & Brainstorming:**
    - A key component for the week.
        - **Focus:** Identify next steps for UX development.
            - **Meeting:** Scheduled for tomorrow.

- **Review & Feedback:**
    - Feedback from WA SMEs will stem from R3 work.
    - Link to the brainstorm session for insights.
    - Tasks will be conducted in parallel.

### 3. Assigned Tasks
- **Rich Text Editor:** Assigned to Rich.
- **Image Upload:** Assigned to Rich.
- **AI Smart Checklist Experiment:**
    - Sharing GPT-O1 recommendations.
    - **Preview Rules:**
        - The generated rule performed well.
        - AI can generate code that typically wouldn't be written.
            - **Eric's Work:**
                - Developing a script for a rule to conduct testing.
                    - Primarily letting AI generate the code.
                    - Starting to integrate various components.
    - **Preview Smart Checklist:** Under development.

### 4. Flux Implementation
- **Next Steps:**
    - Gather information regarding Flux.
    - Create a use case for Flux.


---  

# Demo Notes - September 27

## General Overview
- **Demo Link:** [WA Reviews](https://wa-reviews.hosting.cornell.edu/)
- **Rich Text Editor:**
    - Rich has looked into the tasks and is currently making progress.

- **GPT-O1:**
    - Eric believes it is fundamentally improving processes.

## Demo Highlights
- **AI Integration:**
    - Demonstration of how AI is being utilized to identify issues.
    - Feedback sought primarily from Annie and Sean.
        - Reached a branching point regarding WA and rule-based compliance testing.

- **Scope Definition:**
    - Once the scope is defined, it will mirror the AA Checklist within the WA review project site.

- **Current Testing:**
    - Four rules are being tested.
    - Code analyzes HTML and generates applicable outputs:
        - Users can press the AI button for context on the page.
            - Displays pass/fail status and the reasoning behind it.
            - Accuracy is crucial; outputs can fluctuate between pass and fail.
            - Eric believes we have a solid framework to make this functional.

- **Failing Guidelines:**
    - After clicking "Add Issue" on a failed guideline, the relevant guideline displays correctly.

## Feedback
- **Sean:**
    - Appreciates the direction but finds it overwhelming.
    - Concerned about AI's indecisiveness leading to user confusion.
    - Likes that it identifies elements but finds the selector technical.
        - Suggestion: Provide a visual description of where elements might be on the page.
            - Eric noted this could be improved.
            - Interesting to have AI generate descriptive content linked to approximate line numbers.
    - Observed that some functionalities resemble those in Wave and hopes for more innovative ideas.

- **Annie:**
    - Confused by the presented issue regarding the "restart slideshow button."
        - Noted that the onclick name is causing confusion.
            - Eric confirmed it identifies the correct concern despite the mix-up.
    - Appreciates the suggestion feature for additional checks beyond incorrect pieces.
    - Envisions an interface distinguishing between AI and human identification of issues.
        - Wave currently assists with this.

## Continued Discussion
- Reviewing resources:
    - [Deque University Rules](https://dequeuniversity.com/rules/axe/4.10/#wcag-22-level-a--aa-rules)
    - [W3C ACT Rules](https://www.w3.org/WAI/standards-guidelines/act/rules/)
    - Some ACT rules may not apply to our current code.
        - ACT's core GitHub may provide useful materials: [axe-core GitHub](https://github.com/dequelabs/axe-core).
    - Certain ACT rules are absent due to outdated HTML elements (e.g., Marquee).

- **Eric's Thoughts:**
    - Interested in linking ACT rules but concerned about the relationship between our guidelines and ACT rules.

- **Sean's Input:**
    - AI and LLMs can be beneficial for quickly digesting WCAG data.
    - It would require logical understanding to handle particular cases, making it an experimental endeavor.

- **Eric's Concerns:**
    - Addressing false confidence in AI outputs:
        - Need strategies to manage false examples and repair code effectively.
        - Missing guidelines could undermine trust in the system.
    - ACT rules allow easy testing against established standards.

## Questions
- Regarding our guidelines, should we build against WAI standard guidelines?
    - Sean noted that relying solely on ACT rules could create gaps.
    - Our rules are designed for human readability, capturing comprehensive coverage.

## Next Steps
- **Action Items:**
    - Eric needs assistance from Annie and Sean to compile a list of "Suggested Rules."
        - Walk through guidelines to identify what is covered by ACT rules.
        - For uncovered items, they need to be included in the list.
    - Sean has time today to review ACT rules.
        - Eric suggested linking ACT rules to our guidelines for better association:
            - "Associate this ACT rule with this guideline."


---  

# Demo Notes - September 30

## Discussion Points

### 1. Feedback Review
- **High-Quality Feedback:**
    - Received positive feedback from last week.
    - Directionally, we appear to be improving.
    - Open discussion on tasks for the week.

### 2. R4 Review
- **Current Status:**
    - Real app use has not yet been achieved.
    - Feedback from WA SMEs has been successfully collected.

- **AI Smart Checklists Experiment:**
    - Generated significant discussion on future directions.
    - Marked as completed.

- **Implementing Flux:**
    - Uncertainty about its utilization for the current project.
    - Approval and P Card process would consume additional time.

### 3. Approaches to Completion
- **Two Possible Paths:**
    - **Letting the Experiment Run Out:**
        - Risks not achieving a usable product.
        - Not looking to pursue this option.

    - **Getting to an MVP:**
        - Aim for real app use and effective reporting.
        - Guideline checklist and linking to the rules are essential for utility from a WA perspective.
        - UI components are also crucial:
            - Flux could aid in enhancing usability.
            - Moving from the current state to a more functional level requires additional resources.

### 4. Resource Considerations
- **Budget and Time:**
    - More than just this week’s effort is needed.
    - Assess actual effort spent to determine if we can secure additional development time.
    - Transitioning from draft to usability will require significant resources.

### 5. Project Vision
- **Future Work and AI Research:**
    - Eric feels there will be follow-up work related to AI research experimentation.
    - Discussion on next steps and learnings from this project.
    - Emphasis on using AI to build applications.
    - Important internal AI experiment to understand application development through AI.

### 6. Nick's Thoughts
- **Rich Update on Replicon:**
    - David and Nick will run the report to assess remaining time.
    - Aim to utilize all available time.

- **Flux Timeline:**
    - If Flux is obtained in the next few weeks, it should not be detrimental.
    - Current planning should ensure all tasks are completed.

- **Project Learning Goals:**
    - This project aims to enhance understanding of AI utilization and share findings with the AI team.
    - Proposal for a potential phase two of the project.
        - Possibility for further work on different applications in subsequent phases.

### 7. Eric's Thoughts
- **Collaboration:**
    - Interest in sharing information and collaborating with Ayham on ongoing experimentation.

- **Current Needs:**
    - Proceeding with the project is possible even without Flux at this moment; it is not a must-have.

- **Flux Pricing:**
    - Introductory pricing for Flux may not last much longer (Developer's note).

## Key Pieces/Next Steps
- **Allocate Additional Time:**
    - Determine if another week can be allocated to R4-R5.
        - **R4:** Focus on data entry aspects.
        - **R5:** Concentrate on reporting functionalities in preparation for initial use.

- **Rich's Tasks:**
    - Work on the rich text editor and image upload components.


---  

# Meeting Notes - October 4

## Demo Overview
- **Guideline Checklist:**
    - Working on the guideline checklist this week.

- **UX Rework:**
    - Eric focused on reworking the UX.
        - Not much AI code in the application, but it has been transformed.

- **WA-Review App:**
    - Scope is being added.
        - Once completed, guidelines can be generated.
        - Concepts of sorting, automated rules, and manual review are being integrated.
            - A search criterion within the guidelines is planned as a starting point for UX enhancements.

- **Scope Analysis:**
    - New page analyzer component introduced.
    - Currently, there are four automated rules in testing.

- **Guidelines Mapping:**
    - Sean provided a mapping of our guidelines to compliance test rules.
        - Future discussions needed on how to build this out.
        - Linking guidelines to compliance rules is beneficial.

- **AI Prompt Development:**
    - Aiming to create an AI prompt to identify applicable functions.
        - "Find the applicable functions."

## Scope Analysis Details
- **Clickable Rules:**
    - Users can click to see applicable rules (currently four).
    - Interaction with AI provides page content and ACT rules for the page.
        - Displays target pass/fail status and reasoning.
        - Option to "Create Issue" when a rule fails.
            - This section utilizes AI effectively.
            - AI Prompt playground is used to target specific failures and descriptions, providing applicable guidelines on the screen.
            - Users can consult AI Assistance Chat for more details (e.g., "More detail on how to fix this issue" yields GPT-4o mini solution).
    - Progress completion will be shown once issues are identified and reviewed.

## Questions and Feedback
- **Dawn:**
    - **Guidelines Review:**
        - Asks if each scope line needs to be checked off.
            - Currently, yes.
            - Discussion on how much can be automated if rules are consistently applicable.
                - Non-applicable rules can be identified.
                - Additional search capabilities can be built.
        - Concern about 99 completed clicks impacting progress.
            - Eric has not yet built out rules related to guidelines.
                - This may become a focused task for next week.

- **Ayham:**
    - Inquired about running experiments with AI without executing functions.
        - Yes, this was done.
    - Asked about using a framework for function execution.
        - AI was used to generate functions, which were then analyzed using GPT-o1 preview.
    - Question on rule detection methods:
        - Some rules require visual inspection.
        - Ongoing inquiry into the extent of automation possible.
    - Discussed the overlap between tools being used and existing sites.
        - Site Improve already covers some guidelines.

- **Trust in the System:**
    - A major component for success is whether users trust the system.

## Continued Demo
- **Interface Improvements:**
    - Eric focused on creating a more user-friendly interface.
        - Tested responses from Chat GPT-o1 preview.
            - Provided good results and a plan for general upgrades.
            - Confirmed requirements and layout for the dashboard, which was helpful for Eric.

- **Impact of Chat GPT-o1:**
    - Fundamentally changed capabilities for AI as software developers.

## Additional Feedback
- **David:**
    - Highlighted the main goal of the experiment: to identify changes in perspective regarding what the team can achieve with AI.

- **Eric:**
    - Has not extensively tested autocomplete code.
        - GPT-o1 has simplified the process, saving time and effort.

- **Rich:**
    - Suggested exploring other AI applications for experimentation as a topic for next week.

## Next Steps
- **Key Actions:**
    - Focus on making the application useful for users.
        - Ensure easy navigation through obvious guidelines.
        - Automate processes through Site Improve for better filtered lists.
        - Expand the number of automated rules.

- **Upcoming Meetings:**
    - Early next week, compile feedback from R4/R5.
        - David will schedule a meeting for early next week.


---  

# Planning Meeting Notes - October 7

## Discussion Points

### 1. Flux Tool
- **Overview:**
    - Brief discussion in the devs meeting last week.
    - Opportunity for more team members to discuss the tool.

### 2. Work Focus
- **Current Priorities:**
    - Avoid focusing on project reports for now.
    - Concentrate on making the tool useful for immediate needs.
        - **Key Component:** Filtering guidelines.
            - A functional outcome that will enable us to gather feedback.
            - WA-AI Compile Feedback will address part of this.

- **Additional Considerations:**
    - Image upload may not be a crucial component.
    - **Rich Text Editor:**
        - Issues encountered in integrating it into the component.
        - Need to revisit this topic.

## Filter Functionality
- **Preparation:**
    - Getting filter functionality ready is essential.
        - N/A filter.
        - Filter Siteimprove guidelines.
        - Implement search and question-asking features on the Scope page.
            - A simple search function needs to be established.

### 3. Rich's Input
- **High-Level Questions:**
    - Are there tools available for asking higher-level questions?
    - Will items covered by Siteimprove be excluded from our guidelines?
        - This would require marking or checking to determine the coverage percentage.
        - Annotating guidelines could serve as a starting point.

- **Direction of Efforts:**
    - What is the overall direction we are taking?
        - Are we reducing the list of guidelines?
        - Eric mentioned that the WA-AI Compile Feedback Meeting will help confirm and finalize our next steps.
        - This week is dedicated to development.
            - Next week will focus on documentation and reporting.

- **Rich Text Editor:**
    - Rich is sharing relevant code related to the text editor.


---  

# Feedback Meeting Notes - October 8

## Discussion Points
- **Interface Feedback:**
    - Gather feedback on current opportunities within the app's interface.
    - Evaluate if any last development tweaks are necessary.

- **Project Transition:**
    - Eric anticipates needing another mini project to transition from an experiment to a standard tool.

## Feedback

### Dawn
- **Checkbox Concerns:**
    - The checkboxes may add extra work for users.
        - Question: Is it necessary for users to check these boxes?

- **User Navigation:**
    - New users must drill down to access the issues list.
        - Concern: Users cannot view all issues at once.
        - Suggestion: Provide a comprehensive list of all issues for documentation purposes.
        - Question: How will this be useful for new users?

### Rich
- **Visual Enhancements:**
    - Suggests implementing badges for easier navigation.
        - Instead of checkboxes, consider using whole rows and columns.
        - A visual breadcrumb trail would help users track where they left off.

### Eric
- **User Insights:**
    - Interested in feedback from Sean and Annie regarding features that could enhance usability.
        - How can we ensure the app is beneficial for users?

### Sean
- **Overall Impressions:**
    - Positive feedback on current developments, especially in adding issues.
        - Marking different checkboxes on the checklist is time-saving.

- **Detail Orientation:**
    - Emphasizes the importance of small details for efficiency.
    - As the app matures, he anticipates providing more in-depth feedback.
        - Eric mentioned interest in a follow-up project with a UX design session at the start.

- **Interface Value:**
    - Even without AI, the interface development has been worthwhile.
        - Annie noted that it is much less cumbersome now.

### Annie
- **Direction and Functionality:**
    - Happy with the app's direction.
        - Sean conducts daily reviews.
        - Interested in involving Hank, as checklists are a key part of his role.

- **User-Friendliness:**
    - The app needs UX improvements to be more accessible for beginners and efficient for frequent users.

## Eric's Specific Questions
- **Project Management:**
    - Are there other fields that should be included at the overall project level from the start?

- **Information Requests:**
    - Request to Annie for additional information to capture at the site review level.
        - What should be included at the scope level?
            - Ensure these views are represented.
        - Annie mentioned that any requests will consist of static information.

- **Guidelines View Update:**
    - Eric has not updated the issues section since starting the applicable guidelines.
        - Question: Should we enhance the guidelines view?
            - Ensure all information is presented accurately.
        - Discussed the complexity of representing issues that involve multiple guidelines.
            - Annie agreed, noting that while one core issue exists, it often has a web of related issues.
            - Question: Should these be separated or intertwined?

### Sean's Input
- **Assessment Challenges:**
    - When conducting assessments, he begins on the home page.
        - This might reveal systemic issues affecting multiple pages.
    - Highlighted the difficulty in capturing differently structured issues.

## Proposed Structure
- **Scope and Categorization:**
    - Eric suggested having a scope bucket with titles such as:
        - Site Template
        - Standard Components
    - This would aid in categorizing issues.

- **Observations vs. Guidelines:**
    - In issues, "Observations" could transform into "Guidelines."
    - Complex issues currently involve a multi-step process.
    - A structured approach within a scope is essential.


---  

# Demo Notes - October 11

## Demo Overview
- **Guidelines Interface Updates:**
    - Removed one of the columns and added dropdowns.
    - Introduced tagging features in the tool:
        - Reviewed tools and requirements section (the script).
            - Examples include screen reader and keyboard accessibility notes.
            - Tags are now filterable.
            - This marks the first step toward displaying automated guidelines.
                - Need to double-check and verify some information.
                - Added a "Mark as Completed" feature through Siteimprove.
            - Created a multimedia tag to address some N/A sections.
    - Aiming to increase the number of automated task completions.
    - Eric plans to implement a search feature to find applicable guidelines.

- **AI Prompt Testing:**
    - Developed an AI prompt for the "Assess with AI" button.
    - Eric has yet to refine the AI-generated prompt.
    - The AI produced a step-by-step process for the prompt.

- **Assess with AI:**
    - GPT-4o employs a methodical process.
    - GPT-o1 is likely the preferred method for future projects.
    - Rich noted that we can use the AI-generated code for adjustments.

- **Project Status:**
    - Eric feels we are at a solid point in concluding this experiment.
    - If pursuing full implementation, Sean will help break down the "See Notes" section.

- **User Interface Improvement:**
    - Eric simplified the process of clicking the checkbox.

## Feedback

### Sean
- **Utility Concerns:**
    - Uncertain about the utility of marking something as completed.
        - Recognizes the utility but notes that issues may be found later during assessments.
        - Eric suggested some checks may only require a quick verification.
            - Some checks pertain to Siteimprove.
            - Consider changing "Complete" to "Reviewed."
            - Suggested displaying "Progress: x% Complete" might be misleading.
            - Proposes adding status indicators at the Scope level.
    - **Manual Checks:**
        - Cautions against assuming Siteimprove coverage means completion; some items still require manual verification.
        - Acknowledged that Siteimprove isn't always 100% accurate.

### Annie
- **Status Focus:**
    - Advocated for focusing on status and percent completion within the testing process.
        - Eric agreed that dropping the % Complete metric may be appropriate.
    - Suggested allowing users to try the tool a few times to assess its utility.
        - Views it as a potentially useful, optional tool for supervisors and training.
    - Clarified that it won't currently serve as a substitute for traditional methods.
        - Will be included in overall training.
    - **Verification Needs:**
        - Emphasized the need to verify automated tools.
            - Eric mentioned the necessity for assessing the assessments.
            - Proposed establishing a confidence level and feedback loop for the tool.

### Eric
- **Guidelines Utility:**
    - Described guidelines as helpful resources.
    - The "Issues" list is the main output of the project.
        - Example: Identifying target issues and providing descriptions.
    - Rich mentioned the usefulness of a reference list.
    - Expressed uncertainty about the utility beyond capturing issues.
    - Suggested consistently presenting reviewed items on the page.

### Dawn
- **Next Steps:**
    - Inquired about the future of the project.
        - Discussed a possible next phase to complete the internal tool.
            - The internal tool's direction will depend on leadership decisions.
        - Noted significant interface improvements, which will be presented to the team as a draft tool.

## Planning for Next Week
- **Transition to R7:**
    - Moving towards project report-related tasks.
    - Preparing for Demo Day to discuss opportunities externally (e.g., at a conference).

- **Report Preparation:**
    - Eric seeks input on additional tasks or actions needed.

- **Future Meetings:**
    - Nick suggested meeting after next week for next steps/version 2.
    - David proposed a "Lessons Learned" session for the following week.

- **Draft Progress Report:**
    - First draft will be created in Google Docs for team feedback.

