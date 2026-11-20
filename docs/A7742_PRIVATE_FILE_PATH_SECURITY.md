# A7.7.4.2 — Private File Path and Download Hardening

## Findings in reviewed `Big-Changes` source

1. `WorkspaceTaskSubmissionController::directDownload()` and `zipFiles()` only checked a path prefix and `is_file`. Relative traversals or symbolic links could escape the intended private folder if a malformed `stored_path` was present.
2. `WorkspaceTaskSubmissionController::removeFile()` passed a database-stored path directly to `Storage::delete()`; now the stored value is allowlisted before deleting its DB record.
3. `ContractController::download()` read the persisted `document_path` without checking whether it matched the upload folder. Now it accepts only one-level `contracts/*.pdf` and resolves the underlying physical file within private contracts scope.

## Safeguards

- Paths are bound to the exact UUID task and assignment IDs for submitted files.
- Submission filenames must be UUID plus one of the existing upload extensions.
- Backslashes, traversal components, foreign folders, and symlink escapes are denied.
- Contract filenames are restricted to a single PDF filename under `contracts/`.
- Paths are resolved with `realpath()` after validation and compared to the expected absolute scope. This is defense in depth, not a substitute for ACL checks.
- Task ownership/assignment checks, role middleware, and existing workflow checks remain unchanged.
- Invalid/missing files in ZIP downloads still cancel the ZIP rather than returning a partial archive.

## Known limitations / next checks

- This does not change the public Announcements storage policy or contract/customer data scopes; evaluate those separately.
- It is intentionally fail-closed for noncanonical historical filenames, which may need verified migration before being available again.
- Verify authorized direct/ZIP downloads and contract downloads manually; ensure unauthorized users get a denial.
- Symlink replacement between verification and opening is a residual filesystem race; maintain exclusive OS write permissions on the storage root.
- Existing `ZipArchive` temporary-file lifecycle is not changed in this patch; verify OS temporary directory permissions during deployment.
- SQL reset and synthetic integration fixtures are deferred until A7.9, after schema freeze.
