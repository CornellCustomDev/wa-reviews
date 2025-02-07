# Utilization of AI in Building a Web Accessibility Review Application

## Abstract

This project explores the integration of artificial intelligence (AI) into the web accessibility review process to reduce manual effort and enhance efficiency. By developing a web application that incorporates AI capabilities, we aimed to automate the mapping of identified accessibility issues to relevant WCAG guidelines and provide actionable recommendations. Utilizing an experimental and iterative approach, the team designed, developed, and tested the application with continuous feedback from stakeholders. The results indicate that AI can significantly streamline the accessibility review process, reducing time and effort while maintaining accuracy. This report details the methods employed, the outcomes achieved, and discusses the implications for future development and potential university-wide adoption.

## Introduction

The [WCAG Checklist](https://it.cornell.edu/accessibility/wcag-aa-checklist) and [Cornell’s WA Testing Guidelines](https://docs.google.com/document/d/16b77RZcTL0bWZXTkMgJNd0rfkrv-x83jktacVB8NY-U/edit#heading=h.xyjz8p55adkr) provide a robust framework for website accessibility reviews. While they have been successfully used and improved by Custom Development for several years, they remain time-consuming and can be challenging to use (see [WA Checklist Review & Feedback](https://docs.google.com/document/d/1pHC6uhSfrjmsxVKOHt6hLxABYQTqi8aL91m94gLWpCw/edit)). This project aimed to reduce the effort involved in web accessibility reviews by creating a web application and integrating artificial intelligence into the process. The project also sought to build in-house expertise in AI integration to support future Custom Development projects.

The hypothesis was that AI could be used to apply the knowledge in the Guidelines document to articulate failed criteria and provide recommendations for addressing review findings. This could significantly speed up the review process and improve reporting for clients. It could also be the foundation for providing an externally-facing tool for use across the university by anyone who does digital accessibility work.

As an internally-used tool, the web application would be a low-risk deployment with rapid development and feedback cycles. This would allow Custom Development to safely experiment with AI while expanding and refining site-building tools and libraries. It would provide background for Custom Development to offer AI integration services in the future, and create a platform for a university-wide accessibility tool.

## Methods

### Experimental Design Approach

We adopted an agile, experimental approach to the design and development of the web application. This methodology allowed for iterative development cycles, rapid prototyping, and continuous feedback integration from stakeholders and end-users. The key components of our methods included:

1. **Team Formation and Roles**: A multidisciplinary team was assembled, including key stakeholders (Annie, Sean, Ryann), developers (Eric, Rich), a UX designer (Dawn), a Scrum Master (David), and a Project Champion (Nick), to ensure diverse perspectives and expertise.

2. **Agile Development Process**: Implemented an Agile Scrum framework with light planning sessions, Kanban boards for task management, and weekly demos with retrospectives to adapt to new insights and feedback.

3. **AI Integration Strategy**: Utilized Azure OpenAI services to integrate AI capabilities into the application.

4. **Rapid Prototyping and Iteration**: Developed minimum viable products (MVPs) in successive iterations, enhancing functionality based on testing results and stakeholder feedback at each stage.

5. **User Testing and Feedback**: Engaged the Web Accessibility team to test the prototypes, providing feedback on usability, utility, and areas for improvement throughout the development process.

6. **Consultation with AI Expert**: Collaborated with Ayham Boucher to optimize AI model performance, refine prompts, and enhance the accuracy of guideline suggestions.

### Development Phases

#### Initial AI Functionality Development

- **Access to Services**: Worked with CIT Cloud Infrastructure & Solutions Engineering to gain access to Azure OpenAI services and set up the necessary infrastructure for AI model integration.
- **Prompt Engineering**: Crafted initial prompts to enable the AI model to suggest applicable WCAG guidelines and resolutions based on user-described accessibility issues.

#### Web Application Development

- **Prototype Creation**: Built a basic web application encapsulating the web accessibility review process.
- **API Integration**: Addressed technical challenges in connecting the application to the Azure OpenAI API, enhancing interoperability and functionality.
- **Basic AI Assistance Chat**: Implemented a chat feature to provide users with AI-generated guideline recommendations and remediation advice.

#### User Interface and Experience Enhancement

- **UX Design Sessions**: Conducted brainstorming and design sessions to improve the user interface, making it more intuitive and user-friendly, based on feedback from testers.
- **Feature Implementation**: Added features such as the AI Assistance Chat, tagging, filtering options, and the ability to store project information and results.

#### Automation and Testing Integration

- **Automated Testing Rules**: Explored the integration of deterministic testing rules into the application to automate the identification of certain accessibility issues where feasible.
- **Scope Analysis Tools**: Developed mechanisms to define the scope of reviews, allowing users to focus on relevant guidelines and streamline the review process.

### Data Collection and Analysis

- **Feedback Meetings**: Held regular meetings with the WA team and other stakeholders to gather qualitative feedback on the application's performance, usability, and accuracy.
- **Design Discussions**: Engaged in design and planning sessions to identify challenges, brainstorm solutions, and adjust development priorities accordingly.
- **User Observations**: Collected anecdotal observations on how users interacted with the application, noting areas of confusion or inefficiency for further refinement.

## Results

### User Feedback and Application Performance

- **Mixed Reactions to AI Suggestions**: Users, including Sean and Annie, reported that the AI was sometimes able to provide relevant guidelines for described issues, but also noted instances where the AI's outputs were confusing, incorrect, or inconsistent.

- **Appreciation for Certain Features**: Features like the AI Assistance Chat and the simplified issue addition process were well-received, with users acknowledging the potential for these features to enhance the review process.

- **Interface Usability Concerns**: Users pointed out that the application interface could be overwhelming or cumbersome, especially for new users or those not familiar with AI outputs.

### Challenges with AI Accuracy and Trust

- **Inconsistent AI Outputs**: The AI sometimes provided inaccurate guideline suggestions or failed to handle specific cases correctly, leading to user confusion.

- **Need for Manual Verification**: Users emphasized that despite AI assistance, manual verification remained essential to ensure the accuracy and completeness of accessibility reviews.

- **Building User Trust**: Ensuring that users trusted the AI-generated recommendations was identified as a key challenge, necessitating improvements in AI performance and transparency.

### Progress Towards an MVP

- **Prototype Development**: The team successfully developed a functioning prototype that incorporated AI assistance, issue tracking, and user interface improvements.

- **User Interface Enhancements**: Iterative improvements led to a more streamlined user interface.

- **Stakeholder Engagement**: Regular feedback from stakeholders guided the development process, ensuring that the application evolved to meet user needs.

## Discussion

The project demonstrated that integrating AI into the web accessibility review process holds potential for reducing manual effort and improving efficiency. However, significant challenges remain in achieving consistent AI accuracy and building user trust.

### Implications for Future Development

- **Need for AI Refinement**: The inconsistent performance of the AI underscores the necessity for ongoing refinement of the AI prompts to improve reliability.

- **Balance Between Automation and Manual Effort**: The project highlighted the importance of balancing AI assistance with manual review to ensure comprehensive and accurate accessibility assessments.

- **User-Centered Design**: The feedback indicated that a focus on user experience is critical, with a need for further UX design efforts to make the tool accessible and efficient for both new and experienced users.

### Limitations

- **Lack of Quantitative Data**: The project did not collect quantitative data on time saved or efficiency gains, limiting the ability to make definitive claims about improvements in the review process.

- **Scope of AI Capabilities**: The AI's ability to handle complex or nuanced accessibility issues was limited, and the integration of more advanced AI models may be necessary.

- **Build versus Buy**: Tools such as Siteimprove are expected to integrate AI into their accessibility testing tools, potentially reducing the need for a custom-built solution.

## Conclusion

The integration of AI into the web accessibility review process shows promise but requires further development to address challenges related to accuracy, usability, and user trust. The experimental, agile approach allowed the team to identify these challenges early and adapt accordingly.

By continuing to refine the AI capabilities and focusing on user-centered design, there is potential to create a tool that significantly enhances the efficiency of web accessibility reviews. This project has also provided valuable insights and experience in AI integration that can benefit future custom development projects.

### Future Work

- **AI Model Enhancement**: Explore the integration of more advanced AI models to improve the accuracy and consistency of guideline suggestions.

- **User Interface Refinement**: Engage in dedicated UX design sessions to further enhance the interface, making it more intuitive and user-friendly.

- **Expand Automated Testing Rules**: Incorporate additional automated testing rules to cover more accessibility guidelines and reduce manual effort.

- **User Training and Documentation**: Develop training materials and documentation to assist users in effectively utilizing the tool.

- **Pilot Testing with Broader Audience**: Conduct pilot testing with a wider group of users to gather more diverse feedback and identify additional areas for improvement.
