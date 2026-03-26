import * as React from "react"
import { GripVerticalIcon } from "lucide-react"
import * as ResizablePrimitive from "react-resizable-panels"

import { cn } from "./utils"

const RP: any = ResizablePrimitive

function ResizablePanelGroup({
  className,
  ...props
}: React.ComponentPropsWithoutRef<"div"> & Record<string, any>) {
  // Σε κάποιες εκδόσεις λέγεται Group αντί PanelGroup
  const Group = RP.PanelGroup ?? RP.Group
  if (!Group) {
    throw new Error(
      "react-resizable-panels: Cannot find PanelGroup/Group export. Check installed package.",
    )
  }

  return (
    <Group
      data-slot="resizable-panel-group"
      className={cn(
        "flex h-full w-full data-[panel-group-direction=vertical]:flex-col",
        className,
      )}
      {...props}
    />
  )
}

function ResizablePanel(props: Record<string, any>) {
  const P = RP.Panel
  if (!P) {
    throw new Error(
      "react-resizable-panels: Cannot find Panel export. Check installed package.",
    )
  }

  return <P data-slot="resizable-panel" {...props} />
}

function ResizableHandle({
  withHandle,
  className,
  ...props
}: Record<string, any> & { withHandle?: boolean; className?: string }) {
  // Σε κάποιες εκδόσεις: PanelResizeHandle / ResizeHandle
  const Handle = RP.PanelResizeHandle ?? RP.ResizeHandle
  if (!Handle) {
    throw new Error(
      "react-resizable-panels: Cannot find PanelResizeHandle/ResizeHandle export. Check installed package.",
    )
  }

  return (
    <Handle
      data-slot="resizable-handle"
      className={cn(
        "bg-border focus-visible:ring-ring relative flex w-px items-center justify-center after:absolute after:inset-y-0 after:left-1/2 after:w-1 after:-translate-x-1/2 focus-visible:ring-1 focus-visible:ring-offset-1 focus-visible:outline-none data-[panel-group-direction=vertical]:h-px data-[panel-group-direction=vertical]:w-full data-[panel-group-direction=vertical]:after:left-0 data-[panel-group-direction=vertical]:after:h-1 data-[panel-group-direction=vertical]:after:w-full data-[panel-group-direction=vertical]:after:-translate-y-1/2 data-[panel-group-direction=vertical]:after:translate-x-0 [&[data-panel-group-direction=vertical]>div]:rotate-90",
        className,
      )}
      {...props}
    >
      {withHandle && (
        <div className="bg-border z-10 flex h-4 w-3 items-center justify-center rounded-xs border">
          <GripVerticalIcon className="size-2.5" />
        </div>
      )}
    </Handle>
  )
}

export { ResizablePanelGroup, ResizablePanel, ResizableHandle }