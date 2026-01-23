# 🔄 ActiveRecord Lifecycle Hooks

Lifecycle hooks are special methods in an `ActiveRecord` model that are automatically called at specific stages of data processing (such as before saving or after deletion). This feature is very similar to the **Yii Framework** style.

---

## 📋 Available Hooks

| Hook           | Execution Time            | Common Uses                                   |
| -------------- | ------------------------- | --------------------------------------------- |
| `beforeSave`   | Before INSERT or UPDATE   | Data validation, password hashing, defaults   |
| `afterSave`    | After successful save     | Activity logging, sending emails, cache clear |
| `beforeDelete` | Before record deletion    | Relation checks (prevent deleting key data)   |
| `afterDelete`  | After successful deletion | File cleanup, deleting related logs           |

---

## 🛠️ Usage Guide

### 1. beforeSave(&$data, $insert)

This method is called before the `INSERT` or `UPDATE` query is executed.

- **`&$data`**: The data to be saved (passed by reference, so you can modify it).
- **`$insert`**: `true` if it's a new record (INSERT), `false` if it's an update.
- **Return**: Must return `true`. If `false`, the save process will be aborted.

#### Example: Automatic Password Hashing

```php
protected function beforeSave(array &$data, bool $insert): bool
{
    // Hash password if it is being created or changed
    if (isset($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
    }

    // Set default values for new records only
    if ($insert) {
        $data['status'] = $data['status'] ?? 'active';
    }

    return true; // Continue the save process
}
```

---

### 2. afterSave($insert, $data)

Called immediately after a successful query and once the ID (for inserts) has been retrieved.

- **`$insert`**: `true` if it's a new record.
- **`$data`**: Final data saved (includes the new ID).

#### Example: Send Welcome Email

```php
protected function afterSave(bool $insert, array $data): void
{
    if ($insert) {
        // Logic to send welcome email to a new user
        Log::info("New user registered with ID: " . $data['id']);
    }
}
```

---

### 3. beforeDelete($id)

Called before a record is deleted by ID.

- **`$id`**: ID of the record to be deleted.
- **Return**: Return `true` to proceed, or `false` to cancel the deletion.

#### Example: Admin Protection

```php
protected function beforeDelete(int|string $id): bool
{
    // Do not allow deletion of Admin (ID 1)
    if ($id == 1) {
        return false;
    }
    return true;
}
```

---

### 4. afterDelete($id)

Called after the record has been deleted from the database.

#### Example: File Cleanup

```php
protected function afterDelete(int|string $id): void
{
    // Delete profile picture from server after user record is deleted
    Storage::delete("uploads/profiles/{$id}.jpg");
}
```

---

## 💡 Best Practices

1. **Use Reference**: In `beforeSave`, ensure you use the `&` symbol on the `$data` parameter if you want to modify the data representation before it enters the DB.
2. **Keep it Simple**: Avoid putting very heavy business logic in hooks. Use event listeners if the logic is very complex.
3. **Return Boolean**: Do not forget to return `true` in `before...` hooks so that the process is not accidentally interrupted.

---

[⬅️ Back to Docs Index](INDEX.md)
