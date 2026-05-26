# Application Layer Guide

This project follows a scalable structure to keep business logic clean and beginner-friendly.

## Custom Directories

- `Services/`: Use-case orchestration and business workflows.
- `Repositories/`: Data access layer abstraction for complex queries.
- `Actions/`: Single-purpose executable units.
- `Helpers/`: Small reusable helper utilities.
- `Traits/`: Reusable behavior shared by classes.
- `Enums/`: Domain constants and status definitions.
- `Events/`: Domain events triggered by business actions.
- `Listeners/`: React to events with follow-up workflows.
- `Jobs/`: Queueable background tasks.
- `Notifications/`: Notification classes for mail/database channels.
- `Policies/`: Authorization policies per model/module.

Each module we build next will place code in these folders intentionally.
